<?php

/**
 * Table for Document
 */
class m200000_000000_create_table_documents extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%documents}}', [
            'id' => $this->primaryKey(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%documents}}');
    }
}
