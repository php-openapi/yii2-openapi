<?php

/**
 * Table for Upk
 */
class m200000_000003_delete_table_upks extends \yii\db\Migration
{
    public function up()
    {
        $this->dropTable('{{%upks}}');
    }

    public function down()
    {
        $this->createTable('{{%upks}}', [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(150)->null()->defaultValue(null),
        ]);
    }
}
