<?php

/**
 * Table for B123
 */
class m200000_000001_create_table_b123s extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%b123s}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'c123_id' => $this->integer()->notNull()->comment('desc'),
        ]);
        $this->addForeignKey('fk_b123s_c123_id_c123s_id', '{{%b123s}}', 'c123_id', '{{%c123s}}', 'id');
        $this->addCommentOnColumn('{{%b123s}}', 'c123_id', 'desc');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_b123s_c123_id_c123s_id', '{{%b123s}}');
        $this->dropTable('{{%b123s}}');
    }
}
