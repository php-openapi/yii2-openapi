<?php

/**
 * Table for Pristine
 */
class m200000_000003_create_table_pristines extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%pristines}}', [
            0 => 'custom_id_col integer primary key auto_increment NOT NULL',
            1 => 'name text NOT NULL',
            'tag' => $this->text()->notNull()->defaultValue('4 leg'),
            2 => 'new_col varchar(17) NOT NULL',
            3 => 'col_5 decimal(12,4) NOT NULL',
            4 => 'col_6 decimal(11,2) NOT NULL',
            5 => 'col_7 decimal(10,2) NOT NULL',
            6 => 'col_8 json NOT NULL',
            7 => 'col_9 varchar(9) NOT NULL',
            8 => 'col_10 varchar(10) NOT NULL',
            9 => 'col_11 text NOT NULL',
            10 => 'price decimal(10,2) NOT NULL DEFAULT 0 COMMENT \'price in EUR\'',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%pristines}}');
    }
}
