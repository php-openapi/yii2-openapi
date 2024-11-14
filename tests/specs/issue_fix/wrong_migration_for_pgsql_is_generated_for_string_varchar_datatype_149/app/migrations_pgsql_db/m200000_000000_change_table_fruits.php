<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->string(151)->notNull());
    }

    public function safeDown()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->string(150)->null());
    }
}
