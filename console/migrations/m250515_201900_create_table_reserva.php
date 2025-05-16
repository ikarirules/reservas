<?php

use yii\db\Migration;

class m250515_201900_create_table_reserva extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('reserva', [
            'id' => $this->primaryKey(),
            'nombre' => $this->string()->notNull(),
            'cantidad' => $this->integer()->notNull(),
            'fecha' => $this->date()->notNull(),
            'fecha_create' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'fecha_update' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->append('ON UPDATE CURRENT_TIMESTAMP'),
            'telefono' => $this->string(20)->notNull(),
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m250515_201900_create_table_reserva cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250515_201900_create_table_reserva cannot be reverted.\n";

        return false;
    }
    */
}
