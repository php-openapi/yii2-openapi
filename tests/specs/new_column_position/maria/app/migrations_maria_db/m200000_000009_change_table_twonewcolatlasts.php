<?php

/**
 * Table for Twonewcolatlast
 */
class m200000_000009_change_table_twonewcolatlasts extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%twonewcolatlasts}}', 'name', $this->integer()->notNull()->after('email'));
        $this->addColumn('{{%twonewcolatlasts}}', 'last_name', $this->integer()->notNull());
    }

    public function down()
    {
        $this->dropColumn('{{%twonewcolatlasts}}', 'last_name');
        $this->dropColumn('{{%twonewcolatlasts}}', 'name');
    }
}
