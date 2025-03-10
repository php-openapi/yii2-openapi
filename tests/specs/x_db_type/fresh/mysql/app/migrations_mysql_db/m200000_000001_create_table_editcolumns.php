<?php

/**
 * Table for Editcolumn
 */
class m200000_000001_create_table_editcolumns extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%editcolumns}}', [
            'id' => $this->primaryKey(),
            0 => 'name varchar(255) NOT NULL DEFAULT \'Horse-2\'',
            'tag' => $this->text()->notNull(),
            1 => 'first_name varchar(255) NOT NULL',
            'string_col' => $this->text()->null(),
            2 => 'dec_col decimal(12,2) NOT NULL DEFAULT 3.14',
            3 => 'str_col_def varchar(3) NOT NULL',
            4 => 'json_col text NOT NULL',
            5 => 'json_col_2 json NOT NULL',
            6 => 'numeric_col double precision NOT NULL',
            7 => 'json_col_def_n json NOT NULL',
            8 => 'json_col_def_n_2 json NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%editcolumns}}');
    }
}
