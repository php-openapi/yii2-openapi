<?php

/**
 * Table for Invoice
 */
class m200000_000000_create_table_invoices extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%invoices}}', [
            'id' => $this->primaryKey(),
            'vat_rate' => 'enum("standard", "none") NOT NULL DEFAULT \'standard\'',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%invoices}}');
    }
}
