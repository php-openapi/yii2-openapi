<?php

/**
 * Table for Pristine
 */
class m200000_000001_delete_table_pristines extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('name', '{{%pristines}}');
        $this->dropTable('{{%pristines}}');
    }

    public function safeDown()
    {
        $this->createTable('{{%pristines}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(151)->null()->defaultValue(null),
            'fruit_id' => 'int4 NULL DEFAULT NULL',
        ]);
        $this->addForeignKey('name', '{{%pristines}}', 'fruit_id', 'itt_fruits', 'id');
    }
}
