<?php

/**
 * Table for Product
 */
class m200000_000001_create_table_products extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%products}}', [
            'id' => $this->primaryKey(),
            'vat_rate' => 'enum("standard", "none") NOT NULL DEFAULT \'standard\'',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%products}}');
    }
}
