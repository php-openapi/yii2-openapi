<?php

/**
 * Table for Delivery
 */
class m200000_000000_create_table_deliveries extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%deliveries}}', [
            'id' => $this->primaryKey(),
            'title' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%deliveries}}');
    }
}
