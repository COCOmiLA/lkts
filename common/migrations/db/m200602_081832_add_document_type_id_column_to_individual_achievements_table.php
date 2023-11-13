<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m200602_081832_add_document_type_id_column_to_individual_achievements_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        
        $this->addColumn('{{%individual_achievement}}', 'document_type_id', $this->integer());
        
        $this->createIndex(
            '{{%idx-individual_achievements-document_type_id}}',
            '{{%individual_achievement}}',
            'document_type_id'
        );

        
        $this->addForeignKey(
            '{{%fk-individual_achievements-document_type_id}}',
            '{{%individual_achievement}}',
            'document_type_id',
            '{{%individual_achievements_document_types}}',
            'id',
            'SET NULL'
        );
    }

    


    public function safeDown()
    {
        $this->addColumn('{{%individual_achievement}}', 'document_type', $this->string());
        
        $this->dropForeignKey(
            '{{%fk-individual_achievements-document_type_id}}',
            '{{%individual_achievement}}'
        );

        
        $this->dropIndex(
            '{{%idx-individual_achievements-document_type_id}}',
            '{{%individual_achievement}}'
        );

        $this->dropColumn('{{%individual_achievement}}', 'document_type_id');
    }
}
