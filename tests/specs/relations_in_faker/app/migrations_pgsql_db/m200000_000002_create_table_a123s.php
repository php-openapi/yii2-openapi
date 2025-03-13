<?php

/**
 * Table for A123
 */
class m200000_000002_create_table_a123s extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%a123s}}', [
            'id' => $this->primaryKey(),
            'name' => $this->text()->notNull(),
            'b123_id' => $this->integer()->notNull()->comment('desc'),
        ]);
        $this->addForeignKey('fk_a123s_b123_id_b123s_id', '{{%a123s}}', 'b123_id', '{{%b123s}}', 'id');
        $this->addCommentOnColumn('{{%a123s}}', 'b123_id', 'desc');
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_a123s_b123_id_b123s_id', '{{%a123s}}');
        $this->dropTable('{{%a123s}}');
    }
}
