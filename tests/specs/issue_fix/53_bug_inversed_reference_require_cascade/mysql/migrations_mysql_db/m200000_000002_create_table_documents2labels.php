<?php

/**
 * Table for Documents2Labels
 */
class m200000_000002_create_table_documents2labels extends \yii\db\Migration
{
    public function up()
    {
        $this->createTable('{{%documents2labels}}', [
            'document_id' => $this->integer()->notNull(),
            'label_id' => $this->integer()->notNull(),
        ]);
        $this->addPrimaryKey('pk_document_id_label_id', '{{%documents2labels}}', 'document_id,label_id');
        $this->addForeignKey('fk_documents2labels_document_id_documents_id', '{{%documents2labels}}', 'document_id', '{{%documents}}', 'id', 'CASCADE');
        $this->addForeignKey('fk_documents2labels_label_id_labels_id', '{{%documents2labels}}', 'label_id', '{{%labels}}', 'id', 'CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_documents2labels_label_id_labels_id', '{{%documents2labels}}');
        $this->dropForeignKey('fk_documents2labels_document_id_documents_id', '{{%documents2labels}}');
        $this->dropPrimaryKey('pk_document_id_label_id', '{{%documents2labels}}');
        $this->dropTable('{{%documents2labels}}');
    }
}
