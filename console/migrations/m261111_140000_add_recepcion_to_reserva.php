<?php

use yii\db\Migration;

/**
 * Recepción: estado de la reserva, hora y cantidad real de llegada,
 * y marca para los grupos que llegan sin reserva.
 */
class m261111_140000_add_recepcion_to_reserva extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%reserva}}', 'estado', $this->string(20)->notNull()->defaultValue('pendiente'));
        $this->addColumn('{{%reserva}}', 'hora_llegada', $this->dateTime()->null());
        $this->addColumn('{{%reserva}}', 'personas_llegaron', $this->integer()->null());
        $this->addColumn('{{%reserva}}', 'sin_reserva', $this->boolean()->notNull()->defaultValue(false));

        // Quien llega sin reserva puede no dejar teléfono.
        $this->alterColumn('{{%reserva}}', 'telefono', $this->string(20)->null());

        $this->createIndex('idx-reserva-fecha', '{{%reserva}}', 'fecha');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-reserva-fecha', '{{%reserva}}');
        $this->update('{{%reserva}}', ['telefono' => ''], ['telefono' => null]);
        $this->alterColumn('{{%reserva}}', 'telefono', $this->string(20)->notNull());
        $this->dropColumn('{{%reserva}}', 'sin_reserva');
        $this->dropColumn('{{%reserva}}', 'personas_llegaron');
        $this->dropColumn('{{%reserva}}', 'hora_llegada');
        $this->dropColumn('{{%reserva}}', 'estado');
    }
}
