<?php

/**
 * Table for Fruit
 */
class m200000_000000_create_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%fruits}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%fruits}}');
    }
}
