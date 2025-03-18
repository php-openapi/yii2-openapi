<?php

/**
 * Table for Fruit
 */
class m200000_000001_create_table_fruits extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%fruits}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->null()->defaultValue(null)->comment('desc with \' quote'),
            0 => '"description" double precision NULL DEFAULT NULL',
        ]);
        $this->addCommentOnColumn('{{%fruits}}', 'name', 'desc with \' quote');
        $this->addCommentOnColumn('{{%fruits}}', 'description', 'desc \' 2');
    }

    public function safeDown()
    {
        $this->dropTable('{{%fruits}}');
    }
}
