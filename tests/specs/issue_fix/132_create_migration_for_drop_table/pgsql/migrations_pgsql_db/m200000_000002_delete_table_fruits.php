<?php

/**
 * Table for Fruit
 */
class m200000_000002_delete_table_fruits extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('name2', '{{%fruits}}');
        $this->dropTable('{{%fruits}}');
    }

    public function safeDown()
    {
        $this->createTable('{{%fruits}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->null()->defaultValue(null),
            'food_of' => 'int4 NULL DEFAULT NULL',
        ]);
        $this->addForeignKey('name2', '{{%fruits}}', 'food_of', 'itt_the_animal_table_name', 'id');
    }
}
