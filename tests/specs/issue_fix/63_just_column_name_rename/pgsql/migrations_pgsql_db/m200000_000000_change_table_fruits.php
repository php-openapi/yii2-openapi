<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->renameColumn('{{%fruits}}', 'name', 'name_2');
        $this->renameColumn('{{%fruits}}', 'description', 'description_2');
    }

    public function safeDown()
    {
        $this->renameColumn('{{%fruits}}', 'description_2', 'description');
        $this->renameColumn('{{%fruits}}', 'name_2', 'name');
    }
}
