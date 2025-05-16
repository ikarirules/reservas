<?php

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Reserva;

/**
 * ReservaSearch represents the model behind the search form of `frontend\models\Reserva`.
 */
class ReservaSearch extends Reserva
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'cantidad'], 'integer'],
            [['nombre', 'fecha', 'fecha_create', 'fecha_update', 'telefono'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Reserva::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'cantidad' => $this->cantidad,
            'fecha' => $this->fecha,
            'fecha_create' => $this->fecha_create,
            'fecha_update' => $this->fecha_update,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'telefono', $this->telefono]);

        return $dataProvider;
    }
}
