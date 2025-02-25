<?php

/**
 * Table for Payments
 */
class m200000_000000_create_table_payments extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%payments}}', [
            'id' => $this->primaryKey(),
            'currency' => $this->text()->null(),
            'samples' => 'json NOT NULL',
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%payments}}');
    }
}
