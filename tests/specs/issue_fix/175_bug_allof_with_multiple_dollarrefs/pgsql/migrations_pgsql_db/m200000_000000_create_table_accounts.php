<?php

/**
 * Table for Account
 */
class m200000_000000_create_table_accounts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%accounts}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull()->comment('account name'),
            'paymentMethodName' => $this->text()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%accounts}}');
    }
}
