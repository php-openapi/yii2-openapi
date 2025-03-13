<?php

/**
 * Table for Userx
 */
class m200000_000000_create_table_userxes extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%userxes}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%userxes}}');
    }
}
