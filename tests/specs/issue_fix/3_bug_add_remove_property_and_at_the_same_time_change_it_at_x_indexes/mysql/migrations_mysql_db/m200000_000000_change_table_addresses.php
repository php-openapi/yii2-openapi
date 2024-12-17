<?php

/**
 * Table for Address
 */
class m200000_000000_change_table_addresses extends \yii\db\Migration
{
    public function up()
    {
        $this->dropIndex('addresses_shortName_postalCode_key', '{{%addresses}}');
        $this->renameColumn('{{%addresses}}', 'postalCode', 'postCode');
        $this->createIndex('addresses_shortName_postCode_key', '{{%addresses}}', ["shortName", "postCode"], true);
    }

    public function down()
    {
        $this->dropIndex('addresses_shortName_postCode_key', '{{%addresses}}');
        $this->renameColumn('{{%addresses}}', 'postCode', 'postalCode');
        $this->createIndex('addresses_shortName_postalCode_key', '{{%addresses}}', ["shortName", "postalCode"], true);
    }
}
