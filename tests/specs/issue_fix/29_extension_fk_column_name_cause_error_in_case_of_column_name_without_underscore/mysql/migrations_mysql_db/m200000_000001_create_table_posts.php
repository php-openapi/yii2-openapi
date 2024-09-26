<?php

/**
 * Table for Post
 */
class m200000_000001_create_table_posts extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%posts}}', [
            'id' => $this->primaryKey(),
            'content' => $this->text()->null(),
            'user' => $this->integer()->null()->defaultValue(null),
        ]);
        $this->addForeignKey('fk_posts_user_users_id', '{{%posts}}', 'user', '{{%users}}', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_posts_user_users_id', '{{%posts}}');
        $this->dropTable('{{%posts}}');
    }
}
