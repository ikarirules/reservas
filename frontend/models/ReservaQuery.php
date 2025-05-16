<?php

namespace frontend\models;

/**
 * This is the ActiveQuery class for [[Reserva]].
 *
 * @see Reserva
 */
class ReservaQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Reserva[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Reserva|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
