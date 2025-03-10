<?php

/**
 * Table for Alldbdatatype
 */
class m200000_000000_create_table_alldbdatatypes extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%alldbdatatypes}}', [
            'id' => $this->bigPrimaryKey(),
            0 => 'string_col varchar(255) NOT NULL',
            1 => 'varchar_col varchar(132) NOT NULL',
            2 => 'text_col text NOT NULL',
            3 => 'varchar_4_col varchar(4) NOT NULL',
            4 => 'char_4_col char(4) NOT NULL',
            5 => 'char_5_col char NOT NULL',
            6 => 'char_6_col char NOT NULL',
            7 => 'char_7_col char(6) NOT NULL',
            8 => 'char_8_col char NOT NULL DEFAULT \'d\'',
            9 => 'decimal_col decimal(12,3) NOT NULL',
            10 => 'varbinary_col varbinary(5) NOT NULL',
            11 => 'blob_col blob NOT NULL',
            12 => 'bit_col bit NOT NULL',
            13 => 'bit_2 bit(1) NOT NULL',
            14 => 'bit_3 bit(64) NOT NULL',
            15 => 'ti tinyint NOT NULL',
            16 => 'ti_2 tinyint(1) NOT NULL',
            17 => 'ti_3 tinyint(2) NOT NULL',
            18 => 'si_col smallint NOT NULL',
            19 => 'si_col_2 smallint unsigned zerofill NOT NULL',
            20 => 'mi mediumint(10) unsigned zerofill comment "comment" NOT NULL DEFAULT 7',
            21 => 'bi bigint NOT NULL',
            22 => 'int_col int NOT NULL',
            23 => 'int_col_2 integer NOT NULL',
            24 => 'numeric_col numeric NOT NULL',
            25 => 'float_col float NOT NULL',
            26 => 'float_2 float(10, 2) NOT NULL',
            27 => 'float_3 float(8) NOT NULL',
            28 => 'double_col double NOT NULL',
            29 => 'double_p double precision(10,2) NOT NULL',
            30 => 'double_p_2 double precision NOT NULL',
            31 => 'real_col real NOT NULL',
            32 => 'date_col date NOT NULL',
            33 => 'time_col time NOT NULL',
            34 => 'datetime_col datetime NOT NULL',
            35 => 'timestamp_col timestamp NOT NULL',
            36 => 'year_col year NOT NULL',
            37 => 'json_col json NOT NULL',
            38 => 'json_col_def json NOT NULL',
            39 => 'json_col_def_2 json NOT NULL',
            40 => 'blob_def blob NOT NULL',
            41 => 'text_def text NOT NULL',
            42 => 'json_def json NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%alldbdatatypes}}');
    }
}
