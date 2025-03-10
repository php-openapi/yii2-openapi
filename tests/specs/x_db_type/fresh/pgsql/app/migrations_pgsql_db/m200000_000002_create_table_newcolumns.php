<?php

/**
 * Table for Newcolumn
 */
class m200000_000002_create_table_newcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%newcolumns}}', [
            'id' => $this->primaryKey(),
            0 => '"name" varchar NOT NULL',
            1 => '"first_name" varchar NOT NULL',
            'last_name' => $this->text()->notNull(),
            2 => '"dec_col" decimal(12,4) NOT NULL',
            3 => '"json_col" json NOT NULL',
            4 => '"varchar_col" varchar NOT NULL',
            5 => '"numeric_col" double precision NOT NULL',
            6 => '"json_col_def_n" json NOT NULL DEFAULT \'[]\'',
            7 => '"json_col_def_n_2" json NOT NULL DEFAULT \'[]\'',
            8 => '"text_col_array" text[] NOT NULL',
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%newcolumns}}');
    }
}
