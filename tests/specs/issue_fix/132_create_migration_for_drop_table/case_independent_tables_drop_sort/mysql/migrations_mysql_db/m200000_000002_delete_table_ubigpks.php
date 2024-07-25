<?php

/**
 * Table for Ubigpk
 */
class m200000_000002_delete_table_ubigpks extends \yii\db\Migration
{
    public function up()
    {
        $this->dropTable('{{%ubigpks}}');
    }

    public function down()
    {
        $this->createTable('{{%ubigpks}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'name' => $this->string(150)->null()->defaultValue(null),
            'size' => 'enum("x-small", "small", "medium", "large", "x-large") NOT NULL DEFAULT \'x-small\'',
            'd' => 'smallint(5) unsigned zerofill NULL DEFAULT NULL',
            'e' => 'mediumint(8) unsigned zerofill NULL DEFAULT NULL',
            'f' => 'decimal(12,4) NULL DEFAULT NULL',
            'dp' => $this->double()->null()->defaultValue(null),
            'dp2' => 'double(10,4) NULL DEFAULT NULL',
        ]);
    }
}
