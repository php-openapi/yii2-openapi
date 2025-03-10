<?php

/**
 * Table for Animal
 */
class m200000_000000_change_table_animals extends \yii\db\Migration
{
    public function up()
    {
        $this->addColumn('{{%animals}}', 'new_col', $this->text()->notNull()->comment('new col added'));
        $this->dropColumn('{{%animals}}', 'drop_col');
        $this->alterColumn('{{%animals}}', 'name', $this->integer()->notNull());
        $this->alterColumn('{{%animals}}', 'g', $this->text()->notNull()->comment('desc for g'));
        $this->alterColumn('{{%animals}}', 'g2', $this->text()->notNull()->comment('changed comment on g2 col'));
        $this->alterColumn('{{%animals}}', 'g4', $this->integer()->notNull()->comment('data type changes but comment remains same'));
    }

    public function down()
    {
        $this->alterColumn('{{%animals}}', 'g4', $this->text()->notNull()->comment('data type changes but comment remains same'));
        $this->alterColumn('{{%animals}}', 'g2', $this->text()->notNull()->comment('the comment on g2 col'));
        $this->alterColumn('{{%animals}}', 'g', $this->text()->notNull());
        $this->alterColumn('{{%animals}}', 'name', $this->text()->notNull()->comment('the comment on name col'));
        $this->addColumn('{{%animals}}', 'drop_col', $this->text()->notNull());
        $this->dropColumn('{{%animals}}', 'new_col');
    }
}
