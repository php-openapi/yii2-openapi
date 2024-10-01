<?php

/**
 * Table for Fruit
 */
class m200000_000001_create_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%fruits}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null()->comment('desc with \' quote'),
            0 => 'description double precision NULL DEFAULT NULL COMMENT \'desc \\\' 2\'',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%fruits}}');
    }
}
