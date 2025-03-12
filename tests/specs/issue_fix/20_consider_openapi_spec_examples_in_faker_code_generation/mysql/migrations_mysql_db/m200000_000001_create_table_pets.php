<?php

/**
 * Table for Pet
 */
class m200000_000001_create_table_pets extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%pets}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'age' => $this->integer()->null()->defaultValue(null),
            'tags' => 'json NOT NULL',
            'tags_arbit' => 'json NOT NULL',
            'number_arr' => 'json NOT NULL',
            'number_arr_min_uniq' => 'json NOT NULL',
            'int_arr' => 'json NOT NULL',
            'int_arr_min_uniq' => 'json NOT NULL',
            'bool_arr' => 'json NOT NULL',
            'arr_arr_int' => 'json NOT NULL',
            'arr_arr_str' => 'json NOT NULL',
            'arr_arr_arr_str' => 'json NOT NULL',
            'arr_of_obj' => 'json NOT NULL',
            'user_ref_obj_arr' => 'json NOT NULL',
            'one_of_arr' => 'json NOT NULL',
            'one_of_arr_complex' => 'json NOT NULL',
            'one_of_from_multi_ref_arr' => 'json NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%pets}}');
    }
}
