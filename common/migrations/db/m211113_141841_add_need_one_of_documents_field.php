<?php

use yii\db\Migration;




class m211113_141841_add_need_one_of_documents_field extends Migration
{
    


    public function safeUp()
    {
        $this->addColumn('{{%individual_achievements_document_types}}', 'need_one_of_documents', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%attachment_type}}', 'need_one_of_documents', $this->boolean()->defaultValue(false));
        $this->addColumn('{{%dictionary_available_document_types_for_concession}}', 'need_one_of_documents', $this->boolean()->defaultValue(false));
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%individual_achievements_document_types}}', 'need_one_of_documents');
        $this->dropColumn('{{%attachment_type}}', 'need_one_of_documents');
        $this->dropColumn('{{%dictionary_available_document_types_for_concession}}', 'need_one_of_documents');
    }
}
