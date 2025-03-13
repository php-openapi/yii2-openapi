<?php

/**
 * Table for Fruit
 */
class m200000_000000_create_table_fruits extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%fruits}}', [
            'ts' => $this->timestamp()->notNull()->defaultExpression("(CURRENT_TIMESTAMP)"),
            'ts2' => $this->timestamp()->notNull()->defaultValue('2011-11-11 00:00:00'),
            'ts3' => $this->timestamp()->notNull()->defaultValue('2022-11-11 00:00:00'),
            0 => 'ts4 timestamp NOT NULL DEFAULT \'2022-11-11 00:00:00\'',
            1 => 'ts5 timestamp NOT NULL DEFAULT (CURRENT_TIMESTAMP)',
            2 => 'ts6 timestamp NOT NULL DEFAULT \'2000-11-11 00:00:00\'',
            3 => 'd date NOT NULL DEFAULT (CURRENT_DATE + INTERVAL 1 YEAR)',
            4 => 'd2 text NOT NULL DEFAULT (CURRENT_DATE + INTERVAL 1 YEAR)',
            5 => 'd3 text NOT NULL DEFAULT \'text default\'',
            'ts7' => $this->date()->notNull()->defaultExpression("(CURRENT_DATE + INTERVAL 1 YEAR)"),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%fruits}}');
    }
}
