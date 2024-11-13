<?php

/**
 * Table for Label
 */
class m200000_000001_create_table_labels extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%labels}}', [
            'id' => $this->primaryKey(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%labels}}');
    }
}
