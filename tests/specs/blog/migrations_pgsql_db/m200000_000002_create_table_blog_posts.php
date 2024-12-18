<?php

/**
 * Table for Post
 */
class m200000_000002_create_table_blog_posts extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%blog_posts}}', [
            0 => '"uid" varchar(128) NOT NULL',
            'title' => $this->string(255)->notNull(),
            'slug' => $this->string(200)->null()->defaultValue(null),
            'category_id' => $this->integer()->notNull()->comment('Category of posts'),
            'active' => $this->boolean()->notNull()->defaultValue(false),
            'created_at' => $this->date()->null()->defaultValue(null),
            'created_by_id' => $this->integer()->null()->defaultValue(null)->comment('The User'),
        ]);
        $this->addPrimaryKey('pk_blog_posts_uid', '{{%blog_posts}}', 'uid');
        $this->createIndex('blog_posts_title_key', '{{%blog_posts}}', 'title', true);
        $this->createIndex('blog_posts_slug_key', '{{%blog_posts}}', 'slug', true);
        $this->addForeignKey('fk_blog_posts_category_id_categories_id', '{{%blog_posts}}', 'category_id', '{{%categories}}', 'id');
        $this->addForeignKey('fk_blog_posts_created_by_id_users_id', '{{%blog_posts}}', 'created_by_id', '{{%users}}', 'id');
        $this->addCommentOnColumn('{{%blog_posts}}', 'category_id', 'Category of posts');
        $this->addCommentOnColumn('{{%blog_posts}}', 'created_by_id', 'The User');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_blog_posts_created_by_id_users_id', '{{%blog_posts}}');
        $this->dropForeignKey('fk_blog_posts_category_id_categories_id', '{{%blog_posts}}');
        $this->dropIndex('blog_posts_slug_key', '{{%blog_posts}}');
        $this->dropIndex('blog_posts_title_key', '{{%blog_posts}}');
        $this->dropPrimaryKey('pk_blog_posts_uid', '{{%blog_posts}}');
        $this->dropTable('{{%blog_posts}}');
    }
}
