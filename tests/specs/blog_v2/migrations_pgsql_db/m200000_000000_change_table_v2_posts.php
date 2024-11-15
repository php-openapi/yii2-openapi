<?php

/**
 * Table for Post
 */
class m200000_000000_change_table_v2_posts extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%v2_posts}}', 'id', $this->bigPrimaryKey());
        $this->execute('CREATE TYPE "enum_itt_v2_posts_lang" AS ENUM(\'ru\', \'eng\')');
        $this->addColumn('{{%v2_posts}}', 'lang', '"enum_itt_v2_posts_lang" NULL DEFAULT \'ru\'');
        $this->dropIndex('v2_posts_slug_key', '{{%v2_posts}}');
        $this->dropColumn('{{%v2_posts}}', 'uid');
        $this->alterColumn('{{%v2_posts}}', 'category_id', 'int8 NOT NULL USING "category_id"::int8');
        $this->addCommentOnColumn('{{%v2_posts}}', 'category_id', 'Category of posts');
        $this->alterColumn('{{%v2_posts}}', 'active', "DROP DEFAULT");
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', 'int8 NULL USING "created_by_id"::int8');
        $this->addCommentOnColumn('{{%v2_posts}}', 'created_by_id', 'The User');
    }

    public function safeDown()
    {
        $this->dropCommentFromColumn('{{%v2_posts}}', 'created_by_id');
        $this->alterColumn('{{%v2_posts}}', 'created_by_id', 'int4 NULL USING "created_by_id"::int4');
        $this->dropCommentFromColumn('{{%v2_posts}}', 'category_id');
        $this->alterColumn('{{%v2_posts}}', 'category_id', 'int4 NOT NULL USING "category_id"::int4');
        $this->addColumn('{{%v2_posts}}', 'uid', $this->bigInteger()->notNull());
        $this->createIndex('v2_posts_slug_key', '{{%v2_posts}}', 'slug', true);
        $this->dropColumn('{{%v2_posts}}', 'lang');
        $this->dropColumn('{{%v2_posts}}', 'id');
        $this->execute('DROP TYPE "enum_itt_v2_posts_lang"');
        $this->alterColumn('{{%v2_posts}}', 'active', "SET DEFAULT 'f'");
    }
}
