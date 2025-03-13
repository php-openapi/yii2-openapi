<?php

/**
 * Table for Menu
 */
class m200000_000002_create_table_menus extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%menus}}', [
            'id' => $this->bigPrimaryKey(),
            'name' => $this->string(100)->notNull(),
            'parent_id' => $this->bigInteger()->notNull(),
        ]);
        $this->addForeignKey('fk_menus_parent_id_menus_id', '{{%menus}}', 'parent_id', '{{%menus}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_menus_parent_id_menus_id', '{{%menus}}');
        $this->dropTable('{{%menus}}');
    }
}
