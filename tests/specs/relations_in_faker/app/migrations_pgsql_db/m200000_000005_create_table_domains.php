<?php

/**
 * Table for Domain
 */
class m200000_000005_create_table_domains extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%domains}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(128)->notNull()->comment('domain or sub-domain name, in DNS syntax, IDN are converted'),
            'account_id' => $this->integer()->notNull()->comment('user account'),
            0 => '"created_at" timestamp NOT NULL',
        ]);
        $this->addForeignKey('fk_domains_account_id_accounts_id', '{{%domains}}', 'account_id', '{{%accounts}}', 'id');
        $this->addCommentOnColumn('{{%domains}}', 'name', 'domain or sub-domain name, in DNS syntax, IDN are converted');
        $this->addCommentOnColumn('{{%domains}}', 'account_id', 'user account');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_domains_account_id_accounts_id', '{{%domains}}');
        $this->dropTable('{{%domains}}');
    }
}
