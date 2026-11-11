<?php

use yii\db\Migration;

/**
 * Asignación del día: número de mesa del grupo (único por fecha, hay un solo turno)
 * y mozos que lo atienden (uno o varios).
 */
class m261111_150000_add_mesa_mozos_to_reserva extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%reserva}}', 'mesa', $this->integer()->null());
        // Una mesa no puede tener dos grupos el mismo día (los NULL no cuentan).
        $this->createIndex('ux-reserva-fecha-mesa', '{{%reserva}}', ['fecha', 'mesa'], true);

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%reserva_mozo}}', [
            'reserva_id' => $this->integer()->notNull(),
            'mozo_id' => $this->integer()->notNull(),
            'PRIMARY KEY ([[reserva_id]], [[mozo_id]])',
        ], $tableOptions);

        $this->createIndex('idx-reserva_mozo-mozo', '{{%reserva_mozo}}', 'mozo_id');
        $this->addForeignKey('fk-reserva_mozo-reserva', '{{%reserva_mozo}}', 'reserva_id', '{{%reserva}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-reserva_mozo-mozo', '{{%reserva_mozo}}', 'mozo_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%reserva_mozo}}');
        $this->dropIndex('ux-reserva-fecha-mesa', '{{%reserva}}');
        $this->dropColumn('{{%reserva}}', 'mesa');
    }
}
