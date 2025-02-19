<?php

/**
 * Table for Bigpk
 */
class m200000_000006_delete_table_bigpks extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->dropTable('{{%bigpks}}');
    }

    public function safeDown()
    {
        $this->createTable('{{%bigpks}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(150)->null()->defaultValue(null),
        ]);
    }
}
