<?php

/**
 * Table for Ubigpk
 */
class m200000_000007_delete_table_ubigpks extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->dropTable('{{%ubigpks}}');
    }

    public function safeDown()
    {
        $this->createTable('{{%ubigpks}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(150)->null()->defaultValue(null),
            'f' => 'numeric(12,4) NULL DEFAULT NULL',
            'g5' => 'text[] NULL DEFAULT NULL',
            'g6' => 'text[][] NULL DEFAULT NULL',
            'g7' => 'numeric(10,7) NULL DEFAULT NULL',
            'dp' => 'float8 NULL DEFAULT NULL',
        ]);
    }
}
