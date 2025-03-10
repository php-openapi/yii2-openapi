<?php

/**
 * Table for Addtwonewcolinbetweens
 */
class m200000_000001_change_table_addtwonewcolinbetweens extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%addtwonewcolinbetweens}}', 'password', $this->text()->notNull()->after('name'));
        $this->addColumn('{{%addtwonewcolinbetweens}}', 'screen_name', $this->text()->notNull()->after('last_name'));
        $this->addColumn('{{%addtwonewcolinbetweens}}', 'nick_name', $this->text()->notNull()->after('screen_name'));
    }

    public function down()
    {
        $this->dropColumn('{{%addtwonewcolinbetweens}}', 'nick_name');
        $this->dropColumn('{{%addtwonewcolinbetweens}}', 'screen_name');
        $this->dropColumn('{{%addtwonewcolinbetweens}}', 'password');
    }
}
