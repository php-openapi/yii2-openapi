<?php

/**
 * Table for Account
 */
class m200000_000001_create_table_accounts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%accounts}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull()->comment('account name'),
            'paymentMethodName' => $this->text()->null(),
            'user_id' => $this->integer()->null()->defaultValue(null),
            'user2_id' => $this->integer()->null()->defaultValue(null),
            'user3' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_accounts_user_id_users_id', '{{%accounts}}', 'user_id', '{{%users}}', 'id');
        $this->addForeignKey('fk_accounts_user2_id_users_id', '{{%accounts}}', 'user2_id', '{{%users}}', 'id');
        $this->addForeignKey('fk_accounts_user3_users_id', '{{%accounts}}', 'user3', '{{%users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_accounts_user3_users_id', '{{%accounts}}');
        $this->dropForeignKey('fk_accounts_user2_id_users_id', '{{%accounts}}');
        $this->dropForeignKey('fk_accounts_user_id_users_id', '{{%accounts}}');
        $this->dropTable('{{%accounts}}');
    }
}
