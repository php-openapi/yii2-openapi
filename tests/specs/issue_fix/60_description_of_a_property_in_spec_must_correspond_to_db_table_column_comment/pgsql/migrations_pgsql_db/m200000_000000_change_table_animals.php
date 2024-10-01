<?php

/**
 * Table for Animal
 */
class m200000_000000_change_table_animals extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%animals}}', 'new_col', $this->text()->null()->defaultValue(null)->comment('new col added'));
        $this->dropColumn('{{%animals}}', 'drop_col');
        $this->alterColumn('{{%animals}}', 'name', 'int4 NULL USING "name"::int4');
        $this->dropCommentFromColumn('{{%animals}}', 'name');
        $this->addCommentOnColumn('{{%animals}}', 'g', 'desc for g');
        $this->addCommentOnColumn('{{%animals}}', 'g2', 'changed comment on g2 col');
        $this->alterColumn('{{%animals}}', 'g4', 'int4 NULL USING "g4"::int4');
    }

    public function safeDown()
    {
        $this->alterColumn('{{%animals}}', 'g4', 'text NULL USING "g4"::text');
        $this->dropCommentFromColumn('{{%animals}}', 'g2');
        $this->dropCommentFromColumn('{{%animals}}', 'g');
        $this->addCommentOnColumn('{{%animals}}', 'name', 'the comment on name col');
        $this->alterColumn('{{%animals}}', 'name', 'text NULL USING "name"::text');
        $this->addColumn('{{%animals}}', 'drop_col', $this->text()->null()->defaultValue(null));
        $this->dropColumn('{{%animals}}', 'new_col');
    }
}
