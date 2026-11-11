<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Búsqueda del listado de reservas.
 */
class ReservaSearch extends Reserva
{
    const PERIODO_HOY = 'hoy';
    const PERIODO_PROXIMAS = 'proximas';
    const PERIODO_TODAS = 'todas';

    /** @var string texto libre: busca en nombre y teléfono */
    public $q;

    /** @var string hoy | proximas (desde hoy) | todas */
    public $periodo = self::PERIODO_PROXIMAS;

    public function rules()
    {
        return [
            [['q'], 'trim'],
            [['q'], 'string', 'max' => 100],
            [['periodo'], 'in', 'range' => [self::PERIODO_HOY, self::PERIODO_PROXIMAS, self::PERIODO_TODAS]],
            [['fecha'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Para que la URL quede corta: ?q=...&periodo=...
     */
    public function formName()
    {
        return '';
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Reserva::find()->with('mozos');

        $this->load($params);
        if (!$this->validate()) {
            $this->periodo = self::PERIODO_PROXIMAS;
            $this->q = null;
        }

        $hoy = date('Y-m-d');

        // Hoy: por nombre (para encontrar rápido a quien llega).
        // Próximas: lo más cercano primero. Todas: lo más reciente primero.
        $ordenes = [
            self::PERIODO_HOY => ['nombre' => SORT_ASC],
            self::PERIODO_PROXIMAS => ['fecha' => SORT_ASC],
            self::PERIODO_TODAS => ['fecha' => SORT_DESC],
        ];

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => $this->periodo === self::PERIODO_HOY ? 100 : 25],
            'sort' => [
                'attributes' => [
                    'fecha' => [
                        'asc' => ['fecha' => SORT_ASC, 'nombre' => SORT_ASC],
                        'desc' => ['fecha' => SORT_DESC, 'nombre' => SORT_ASC],
                    ],
                    'nombre',
                    'cantidad',
                    'mesa' => [
                        // sin mesa al final
                        'asc' => [new \yii\db\Expression('mesa IS NULL, mesa ASC')],
                        'desc' => [new \yii\db\Expression('mesa IS NULL, mesa DESC')],
                    ],
                ],
                'defaultOrder' => $ordenes[$this->periodo],
            ],
        ]);

        if ($this->periodo === self::PERIODO_HOY) {
            $query->andWhere(['fecha' => $hoy]);
        } elseif ($this->periodo === self::PERIODO_PROXIMAS) {
            $query->andWhere(['>=', 'fecha', $hoy]);
        }

        $query->andFilterWhere(['fecha' => $this->fecha]);

        if ($this->q !== null && $this->q !== '') {
            $query->andWhere(['or',
                ['like', 'nombre', $this->q],
                ['like', 'telefono', $this->q],
            ]);
        }

        return $dataProvider;
    }

    /**
     * Totales del período/búsqueda actual (sin paginar).
     *
     * @return array reservas, personas (reservadas), llegaron (grupos), personas_llegaron, sin_reserva
     */
    public static function totales(ActiveDataProvider $dataProvider): array
    {
        $query = clone $dataProvider->query;
        $query->orderBy(null)->limit(null)->offset(null);

        $fila = $query->select([
            'reservas' => 'COUNT(*)',
            'personas' => 'COALESCE(SUM(CASE WHEN sin_reserva = 0 THEN cantidad ELSE 0 END), 0)',
            'llegaron' => "COALESCE(SUM(CASE WHEN estado = 'llego' THEN 1 ELSE 0 END), 0)",
            'personas_llegaron' => "COALESCE(SUM(CASE WHEN estado = 'llego' THEN personas_llegaron ELSE 0 END), 0)",
            'sin_reserva' => 'COALESCE(SUM(CASE WHEN sin_reserva = 1 THEN 1 ELSE 0 END), 0)',
        ])->asArray()->one();

        return array_map('intval', $fila ?: []);
    }
}
