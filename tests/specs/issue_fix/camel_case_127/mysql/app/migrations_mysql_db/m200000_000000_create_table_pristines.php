<?php

/**
 * Table for Pristine
 */
class m200000_000000_create_table_pristines extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%pristines}}', [
            'id' => $this->primaryKey(),
            'firstName' => $this->text()->notNull(),
            0 => 'newColumn varchar(255) NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%pristines}}');
    }
}
