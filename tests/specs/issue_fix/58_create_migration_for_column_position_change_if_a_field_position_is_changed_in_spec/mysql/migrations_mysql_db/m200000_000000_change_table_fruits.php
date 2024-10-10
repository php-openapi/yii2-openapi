<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->text()->null()->after('id'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->text()->null()->after('description'));
    }
}
