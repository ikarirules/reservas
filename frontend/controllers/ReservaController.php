<?php

namespace frontend\controllers;

use frontend\models\Reserva;
use frontend\models\ReservaSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;


/**
 * ReservaController implements the CRUD actions for Reserva model.
 */
class ReservaController extends Controller
{
    /**
     * @inheritDoc
     */
     public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => \yii\filters\AccessControl::class,
                    'ruleConfig' => ['class' => \common\components\RolAccessRule::class],
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => [\common\models\User::ROL_ADMIN, \common\models\User::ROL_RECEPCIONISTA],
                        ],
                    ],
                ],
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'llegada' => ['POST'],
                        'deshacer-llegada' => ['POST'],
                    ],
                ],
                'timestamp' => [
                    'class' => TimestampBehavior::class,
                    'createdAtAttribute' => 'fecha_create',
                    'updatedAtAttribute' => 'fecha_update',
                    'value' => new Expression('NOW()'),
                ],
            ]
        );
    }

    /**
     * Lists all Reserva models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ReservaSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Recepción: marca que el grupo llegó, con la cantidad real de personas.
     * Si ya estaba marcado, corrige la cantidad (conserva la hora de llegada).
     */
    public function actionLlegada($id)
    {
        $model = $this->findModel($id);
        $personas = (int)$this->request->post('personas', $model->cantidad);
        $corrigiendo = $model->haLlegado();

        if ($model->marcarLlegada($personas)) {
            $diferencia = $personas - (int)$model->cantidad;
            $detalle = $diferencia === 0 ? '' : sprintf(' (%s%d respecto de la reserva)', $diferencia > 0 ? '+' : '', $diferencia);
            $mesa = $model->mesa ? " Mesa {$model->mesa}." : ' Todavía no tiene mesa asignada.';
            Yii::$app->session->setFlash('success', $corrigiendo
                ? "{$model->nombre}: corregido a {$personas} personas{$detalle}."
                : "{$model->nombre} llegó con {$personas} personas{$detalle}.{$mesa}");
        } else {
            Yii::$app->session->setFlash('error', implode(' ', $model->getFirstErrors()));
        }

        return $this->volverAlListado();
    }

    public function actionDeshacerLlegada($id)
    {
        $model = $this->findModel($id);
        $model->deshacerLlegada();
        Yii::$app->session->setFlash('info', "Se deshizo la llegada de {$model->nombre}.");

        return $this->volverAlListado();
    }

    /**
     * Asignación del día: número de mesa y mozos del grupo.
     */
    public function actionAsignar($id)
    {
        $model = new \frontend\models\AsignacionForm($this->findModel($id));

        if ($model->load($this->request->post()) && $model->save()) {
            $reserva = $model->getReserva();
            $mozos = implode(', ', $reserva->getMozos()->select('username')->column());
            Yii::$app->session->setFlash('success', $reserva->nombre
                . ($reserva->mesa ? ": mesa {$reserva->mesa}" : ': sin mesa')
                . ($mozos ? " · {$mozos}" : ''));
            return $this->redirect(['index', 'periodo' => ReservaSearch::PERIODO_HOY]);
        }

        return $this->render('asignar', [
            'model' => $model,
            'carga' => Reserva::cargaMozos($model->getReserva()->fecha),
        ]);
    }

    /**
     * Recepción: un grupo llega sin reserva. Se registra para hoy y ya llegado.
     */
    public function actionSinReserva()
    {
        $model = new Reserva();
        $model->sin_reserva = true;
        $model->fecha = date('Y-m-d');

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->fecha = date('Y-m-d');      // siempre hoy, aunque manden otra cosa
            $model->sin_reserva = true;
            $model->estado = Reserva::ESTADO_LLEGO;
            $model->hora_llegada = date('Y-m-d H:i:s');
            $model->personas_llegaron = $model->cantidad;

            if ($model->save()) {
                Yii::$app->session->setFlash('success', "{$model->nombre} registrado sin reserva, {$model->cantidad} personas. Asignale mesa y mozo.");
                return $this->redirect(['asignar', 'id' => $model->id]);
            }
        }

        return $this->render('sin-reserva', ['model' => $model]);
    }

    /**
     * Vuelve a la pestaña desde donde se hizo la acción (mantiene búsqueda y filtros).
     */
    private function volverAlListado()
    {
        $referrer = (string)$this->request->referrer;
        $listado = $this->request->hostInfo . \yii\helpers\Url::to(['index']);
        if (strpos($referrer, $listado) === 0) {   // solo si viene del listado de este mismo sitio
            return $this->redirect($referrer);
        }
        return $this->redirect(['index', 'periodo' => ReservaSearch::PERIODO_HOY]);
    }

    /**
     * Totales por día de la semana que empieza en $desde (JSON), para el selector de fecha.
     * Respuesta: {"2026-11-14": {"reservas": 3, "personas": 40}, ...}
     *
     * @param string $desde fecha Y-m-d (lunes de la semana)
     */
    public function actionSemana($desde)
    {
        $this->response->format = \yii\web\Response::FORMAT_JSON;

        $inicio = \DateTime::createFromFormat('!Y-m-d', $desde);
        if ($inicio === false || $inicio->format('Y-m-d') !== $desde) {
            throw new \yii\web\BadRequestHttpException('Fecha inválida.');
        }
        $hasta = (clone $inicio)->modify('+6 days')->format('Y-m-d');

        $filas = Reserva::find()
            ->select(['fecha', 'reservas' => 'COUNT(*)', 'personas' => 'SUM(cantidad)'])
            ->where(['between', 'fecha', $desde, $hasta])
            ->groupBy('fecha')
            ->asArray()
            ->all();

        $totales = [];
        foreach ($filas as $fila) {
            $totales[$fila['fecha']] = [
                'reservas' => (int)$fila['reservas'],
                'personas' => (int)$fila['personas'],
            ];
        }

        return (object)$totales;
    }

    /**
     * Displays a single Reserva model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Reserva model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Reserva();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Reserva model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Reserva model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Reserva model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Reserva the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Reserva::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
