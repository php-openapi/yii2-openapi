<?php

/**
 * Table for ColumnNameChange
 */
class m200000_000000_change_table_column_name_changes extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->renameColumn('{{%column_name_changes}}', 'updated_at', 'updated_at_2');
    }

    public function safeDown()
    {
        $this->renameColumn('{{%column_name_changes}}', 'updated_at_2', 'updated_at');
    }
}
