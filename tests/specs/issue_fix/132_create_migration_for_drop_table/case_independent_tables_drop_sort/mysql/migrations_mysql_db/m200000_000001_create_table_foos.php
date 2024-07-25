<?php

/**
 * Table for Foo
 */
class m200000_000001_create_table_foos extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%foos}}', [
            'id' => $this->primaryKey(),
            'factor' => $this->integer()->null()->defaultValue(null),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%foos}}');
    }
}
