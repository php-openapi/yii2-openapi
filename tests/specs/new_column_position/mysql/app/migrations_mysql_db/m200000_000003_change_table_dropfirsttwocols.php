<?php

/**
 * Table for Dropfirsttwocol
 */
class m200000_000003_change_table_dropfirsttwocols extends \yii\db\Migration
{
    public function up()
    {
        $this->dropColumn('{{%dropfirsttwocols}}', 'address');
        $this->dropColumn('{{%dropfirsttwocols}}', 'name');
    }

    public function down()
    {
        $this->addColumn('{{%dropfirsttwocols}}', 'name', $this->text()->null()->first());
        $this->addColumn('{{%dropfirsttwocols}}', 'address', $this->text()->null()->after('name'));
    }
}
