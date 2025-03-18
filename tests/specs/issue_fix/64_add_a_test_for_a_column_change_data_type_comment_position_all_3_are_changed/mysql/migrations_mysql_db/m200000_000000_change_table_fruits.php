<?php

/**
 * Table for Fruit
 */
class m200000_000000_change_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->integer()->null()->defaultValue(null)->after('col')->comment('new desc'));
    }

    public function down()
    {
        $this->alterColumn('{{%fruits}}', 'name', $this->text()->null()->after('id')->comment('desc'));
    }
}
