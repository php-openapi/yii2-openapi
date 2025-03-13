<?php

/**
 * Table for Foo
 */
class m200000_000000_create_table_foos extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%foos}}', [
            'id' => $this->primaryKey(),
            'factor' => $this->integer()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%foos}}');
    }
}
