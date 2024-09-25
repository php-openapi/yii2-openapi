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
            'reference_invoice_id' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_invoices_reference_invoice_id_invoices_id', '{{%invoices}}', 'reference_invoice_id', '{{%invoices}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_invoices_reference_invoice_id_invoices_id', '{{%invoices}}');
        $this->dropTable('{{%invoices}}');
    }
}
