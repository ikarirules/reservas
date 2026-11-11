<?php

use yii\db\Migration;

/**
 * Agrega la categoría (rol) del personal a la tabla user.
 * NULL = usuario sin acceso al panel (ej. registrado desde el frontend).
 */
class m261111_120000_add_rol_to_user extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'rol', $this->string(20)->null()->after('email'));
        $this->createIndex('idx-user-rol', '{{%user}}', 'rol');
    }

    public function safeDown()
    {
        $this->dropIndex('idx-user-rol', '{{%user}}');
        $this->dropColumn('{{%user}}', 'rol');
    }
}
