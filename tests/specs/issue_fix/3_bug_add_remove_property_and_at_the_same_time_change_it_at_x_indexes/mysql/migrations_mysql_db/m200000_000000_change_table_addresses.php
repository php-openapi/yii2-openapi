<?php

/**
 * Table for Address
 */
class m200000_000000_change_table_addresses extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%addresses}}', 'postCode', $this->string(64)->null()->defaultValue(null));
        $this->dropIndex('addresses_shortName_postalCode_key', '{{%addresses}}');
        $this->createIndex('addresses_shortName_postCode_key', '{{%addresses}}', ["shortName", "postCode"], true);
        $this->dropColumn('{{%addresses}}', 'postalCode');
    }

    public function down()
    {
        $this->addColumn('{{%addresses}}', 'postalCode', $this->string(64)->null()->defaultValue(null));
        $this->dropIndex('addresses_shortName_postCode_key', '{{%addresses}}');
        $this->createIndex('addresses_shortName_postalCode_key', '{{%addresses}}', ["shortName", "postalCode"], true);
        $this->dropColumn('{{%addresses}}', 'postCode');
    }
}
