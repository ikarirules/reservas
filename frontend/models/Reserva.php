<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "reserva".
 *
 * @property int $id
 * @property string $nombre
 * @property int $cantidad
 * @property string $fecha
 * @property string|null $fecha_create
 * @property string|null $fecha_update
 * @property string $telefono
 */
class Reserva extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reserva';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nombre', 'cantidad', 'fecha', 'telefono'], 'required'],
            [['cantidad'], 'integer'],
            [['fecha', 'fecha_create', 'fecha_update'], 'safe'],
            [['nombre'], 'string', 'max' => 255],
            [['telefono'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'nombre' => Yii::t('app', 'Nombre'),
            'cantidad' => Yii::t('app', 'Cantidad'),
            'fecha' => Yii::t('app', 'Fecha'),
            'fecha_create' => Yii::t('app', 'Fecha Create'),
            'fecha_update' => Yii::t('app', 'Fecha Update'),
            'telefono' => Yii::t('app', 'Telefono'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return ReservaQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ReservaQuery(get_called_class());
    }

}
