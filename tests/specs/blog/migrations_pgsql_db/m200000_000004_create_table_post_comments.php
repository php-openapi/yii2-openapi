<?php

/**
 * Table for Comment
 */
class m200000_000004_create_table_post_comments extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%post_comments}}', [
            'id' => $this->bigPrimaryKey(),
            'post_id' => $this->string(128)->notNull()->comment('A blog post (uid used as pk for test purposes)'),
            'author_id' => $this->integer()->notNull()->comment('The User'),
            0 => '"message" json NOT NULL DEFAULT \'[]\'',
            1 => '"meta_data" json NOT NULL DEFAULT \'[]\'',
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->addForeignKey('fk_post_comments_post_id_blog_posts_uid', '{{%post_comments}}', 'post_id', '{{%blog_posts}}', 'uid');
        $this->addForeignKey('fk_post_comments_author_id_users_id', '{{%post_comments}}', 'author_id', '{{%users}}', 'id');
        $this->addCommentOnColumn('{{%post_comments}}', 'post_id', 'A blog post (uid used as pk for test purposes)');
        $this->addCommentOnColumn('{{%post_comments}}', 'author_id', 'The User');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_post_comments_author_id_users_id', '{{%post_comments}}');
        $this->dropForeignKey('fk_post_comments_post_id_blog_posts_uid', '{{%post_comments}}');
        $this->dropTable('{{%post_comments}}');
    }
}
