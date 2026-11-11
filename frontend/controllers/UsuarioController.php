<?php

namespace frontend\controllers;

use frontend\models\UsuarioForm;
use common\components\RolAccessRule;
use common\models\User;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Gestión del personal (solo administradores).
 */
class UsuarioController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'ruleConfig' => ['class' => RolAccessRule::class],
                'rules' => [
                    ['allow' => true, 'roles' => [User::ROL_ADMIN]],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find()
                ->where(['not', ['rol' => null]])
                ->andWhere(['<>', 'status', User::STATUS_DELETED]),
            'sort' => ['defaultOrder' => ['rol' => SORT_ASC, 'username' => SORT_ASC]],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionCreate()
    {
        $model = new UsuarioForm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Usuario {$model->username} creado.");
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = new UsuarioForm($this->findModel($id));

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', "Usuario {$model->username} actualizado.");
            return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model]);
    }

    /**
     * Baja lógica: marca la cuenta como eliminada (no se borra de la base).
     */
    public function actionDelete($id)
    {
        $user = $this->findModel($id);

        if ((int)$user->id === (int)Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'No podés eliminar tu propia cuenta.');
        } else {
            $user->status = User::STATUS_DELETED;
            $user->save(false, ['status', 'updated_at']);
            Yii::$app->session->setFlash('success', "Usuario {$user->username} eliminado.");
        }

        return $this->redirect(['index']);
    }

    protected function findModel($id): User
    {
        $user = User::find()
            ->where(['id' => $id])
            ->andWhere(['<>', 'status', User::STATUS_DELETED])
            ->one();

        if ($user === null) {
            throw new NotFoundHttpException('El usuario no existe.');
        }

        return $user;
    }
}
