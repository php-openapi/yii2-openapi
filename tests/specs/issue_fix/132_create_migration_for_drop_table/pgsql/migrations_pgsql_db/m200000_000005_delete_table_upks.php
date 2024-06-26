<?php

/**
 * Table for Upk
 */
class m200000_000005_delete_table_upks extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->dropTable('{{%upks}}');
    }

    public function safeDown()
    {
        $this->createTable('{{%upks}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->null()->defaultValue(null),
            'current_mood' => '"mood" NULL DEFAULT NULL',
            'e2' => '"enum_itt_upks_e2" NULL DEFAULT NULL',
        ]);
    }
}
