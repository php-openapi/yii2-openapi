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
            'tags' => $this->text()->null(),
            'tags_arbit' => $this->text()->null(),
            'number_arr' => $this->text()->null(),
            'number_arr_min_uniq' => $this->text()->null(),
            'int_arr' => $this->text()->null(),
            'int_arr_min_uniq' => $this->text()->null(),
            'bool_arr' => $this->text()->null(),
            'arr_arr_int' => $this->text()->null(),
            'arr_arr_str' => $this->text()->null(),
            'arr_arr_arr_str' => $this->text()->null(),
            'arr_of_obj' => $this->text()->null(),
            'user_ref_obj_arr' => $this->string()->null()->defaultValue(null),
            'one_of_arr' => $this->text()->null(),
            'one_of_arr_complex' => $this->text()->null(),
            'one_of_from_multi_ref_arr' => $this->text()->null(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%pets}}');
    }
}
