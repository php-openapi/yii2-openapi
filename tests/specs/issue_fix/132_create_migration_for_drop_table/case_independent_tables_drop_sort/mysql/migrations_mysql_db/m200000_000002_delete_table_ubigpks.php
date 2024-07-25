<?php

/**
 * Table for Ubigpk
 */
class m200000_000002_delete_table_ubigpks extends \yii\db\Migration
{
    public function up()
    {
        $this->dropTable('{{%ubigpks}}');
    }

    public function down()
    {
        $this->createTable('{{%ubigpks}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(150)->null()->defaultValue(null),
        ]);
    }
}
