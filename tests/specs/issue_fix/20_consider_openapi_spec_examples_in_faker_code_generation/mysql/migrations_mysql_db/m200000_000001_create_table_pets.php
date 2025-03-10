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
            'age' => $this->integer()->notNull(),
            'tags' => $this->text()->notNull(),
            'tags_arbit' => $this->text()->notNull(),
            'number_arr' => $this->text()->notNull(),
            'number_arr_min_uniq' => $this->text()->notNull(),
            'int_arr' => $this->text()->notNull(),
            'int_arr_min_uniq' => $this->text()->notNull(),
            'bool_arr' => $this->text()->notNull(),
            'arr_arr_int' => $this->text()->notNull(),
            'arr_arr_str' => $this->text()->notNull(),
            'arr_arr_arr_str' => $this->text()->notNull(),
            'arr_of_obj' => $this->text()->notNull(),
            'user_ref_obj_arr' => $this->string()->notNull(),
            'one_of_arr' => $this->text()->notNull(),
            'one_of_arr_complex' => $this->text()->notNull(),
            'one_of_from_multi_ref_arr' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%pets}}');
    }
}
