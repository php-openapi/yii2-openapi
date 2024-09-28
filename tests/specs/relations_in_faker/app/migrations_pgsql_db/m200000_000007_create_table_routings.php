<?php

/**
 * Table for Routing
 */
class m200000_000007_create_table_routings extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%routings}}', [
            'id' => $this->primaryKey(),
            'domain_id' => $this->integer()->notNull()->comment('domain'),
            'path' => $this->string(255)->null()->defaultValue(null),
            'ssl' => $this->boolean()->null()->defaultValue(null),
            'redirect_to_ssl' => $this->boolean()->null()->defaultValue(null),
            'service' => $this->string(255)->null()->defaultValue(null),
            0 => '"created_at" timestamp NULL DEFAULT NULL',
            'd123_id' => $this->integer()->null()->defaultValue(null)->comment('desc'),
            'a123_id' => $this->integer()->null()->defaultValue(null)->comment('desc'),
        ]);
        $this->addForeignKey('fk_routings_domain_id_domains_id', '{{%routings}}', 'domain_id', '{{%domains}}', 'id');
        $this->addForeignKey('fk_routings_d123_id_d123s_id', '{{%routings}}', 'd123_id', '{{%d123s}}', 'id');
        $this->addForeignKey('fk_routings_a123_id_a123s_id', '{{%routings}}', 'a123_id', '{{%a123s}}', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_routings_a123_id_a123s_id', '{{%routings}}');
        $this->dropForeignKey('fk_routings_d123_id_d123s_id', '{{%routings}}');
        $this->dropForeignKey('fk_routings_domain_id_domains_id', '{{%routings}}');
        $this->dropTable('{{%routings}}');
    }
}
