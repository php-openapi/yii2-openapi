<?php

/**
 * Table for Sample
 */
class m200000_000001_create_table_samples extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%samples}}', [
            'id' => $this->primaryKey(),
            'message' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%samples}}');
    }
}
