<?php

/**
 * Table for Mango
 */
class m200000_000003_delete_table_the_mango_table_name extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->dropForeignKey('animal_fruit_fk', '{{%the_mango_table_name}}');
        $this->dropTable('{{%the_mango_table_name}}');
    }

    public function safeDown()
    {
        $this->createTable('{{%the_mango_table_name}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->null()->defaultValue(null),
            'food_of' => 'int4 NULL DEFAULT NULL',
        ]);
        $this->addForeignKey('animal_fruit_fk', '{{%the_mango_table_name}}', 'food_of', 'itt_the_animal_table_name', 'id');
    }
}
