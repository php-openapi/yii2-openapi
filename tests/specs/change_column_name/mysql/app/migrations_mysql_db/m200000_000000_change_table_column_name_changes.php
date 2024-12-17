<?php

/**
 * Table for ColumnNameChange
 */
class m200000_000000_change_table_column_name_changes extends \yii\db\Migration
{
    public function up()
    {
        $this->renameColumn('{{%column_name_changes}}', 'updated_at', 'updated_at_2');
    }

    public function down()
    {
        $this->renameColumn('{{%column_name_changes}}', 'updated_at_2', 'updated_at');
    }
}
