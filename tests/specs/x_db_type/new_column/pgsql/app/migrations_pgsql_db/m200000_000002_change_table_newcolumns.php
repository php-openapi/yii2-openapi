<?php

/**
 * Table for Newcolumn
 */
class m200000_000002_change_table_newcolumns extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "first_name" varchar NOT NULL')->execute();
        $this->addColumn('{{%newcolumns}}', 'last_name', $this->text()->notNull());
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "dec_col" decimal(12,4) NOT NULL')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "json_col" json NOT NULL')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "varchar_col" varchar NOT NULL')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "numeric_col" double precision NOT NULL')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "json_col_def_n" json NOT NULL DEFAULT \'[]\'')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "json_col_def_n_2" json NOT NULL DEFAULT \'[]\'')->execute();
        $this->db->createCommand('ALTER TABLE {{%newcolumns}} ADD COLUMN "text_col_array" text[] NOT NULL')->execute();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%newcolumns}}', 'text_col_array');
        $this->dropColumn('{{%newcolumns}}', 'json_col_def_n_2');
        $this->dropColumn('{{%newcolumns}}', 'json_col_def_n');
        $this->dropColumn('{{%newcolumns}}', 'numeric_col');
        $this->dropColumn('{{%newcolumns}}', 'varchar_col');
        $this->dropColumn('{{%newcolumns}}', 'json_col');
        $this->dropColumn('{{%newcolumns}}', 'dec_col');
        $this->dropColumn('{{%newcolumns}}', 'last_name');
        $this->dropColumn('{{%newcolumns}}', 'first_name');
    }
}
