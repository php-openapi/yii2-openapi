<?php

/**
 * Table for Fakerable
 */
class m200000_000003_create_table_fakerable extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%fakerable}}', [
            'id' => $this->bigPrimaryKey(),
            'active' => $this->boolean()->notNull(),
            'floatval' => $this->float()->notNull(),
            'floatval_lim' => $this->float()->notNull(),
            'doubleval' => $this->double()->notNull(),
            'int_min' => $this->integer()->notNull()->defaultValue(3),
            'int_max' => $this->integer()->notNull(),
            'int_minmax' => $this->integer()->notNull(),
            'int_created_at' => $this->integer()->notNull(),
            'int_simple' => $this->integer()->notNull(),
            'str_text' => $this->text()->notNull(),
            'str_varchar' => $this->string(100)->notNull(),
            'str_date' => $this->date()->notNull(),
            'str_datetime' => $this->timestamp()->notNull(),
            'str_country' => $this->text()->notNull(),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%fakerable}}');
    }
}
