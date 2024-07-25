<?php

/**
 * Table for Animal
 */
class m200000_000004_delete_table_the_animal_table_name extends \yii\db\Migration
{
    public function up()
    {
        $this->dropTable('{{%the_animal_table_name}}');
    }

    public function down()
    {
        $this->createTable('{{%the_animal_table_name}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(150)->null()->defaultValue(null),
        ]);
    }
}
