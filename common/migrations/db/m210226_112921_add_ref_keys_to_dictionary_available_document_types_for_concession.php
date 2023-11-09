<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210226_112921_add_ref_keys_to_dictionary_available_document_types_for_concession extends MigrationWithDefaultOptions
{
    private static $columns =  [
        'admission_campaign_ref_id' => 'admission_campaign_reference_type',
        'available_document_type_filter_ref_id' => 'available_document_type_filter_reference_type',
        'document_set_ref_id' => 'document_set_reference_type',
        'document_type_ref_id' => 'dictionary_document_type',
    ];
    
    


    public function safeUp()
    {
        $this->addColumn('{{%dictionary_available_document_types_for_concession}}', 'document_set_code', $this->string());
        
        foreach (self::$columns as $column => $table) {
            $this->addColumn('{{%dictionary_available_document_types_for_concession}}', $column, $this->integer());
            
            $this->createIndex(
                '{{%idx-docs_concession-' . $column . '}}',
                '{{%dictionary_available_document_types_for_concession}}',
                $column
            );

            $this->addForeignKey(
                '{{%fk-docs_concession-' . $column . '}}',
                '{{%dictionary_available_document_types_for_concession}}',
                $column,
                '{{%' . $table . '}}',
                'id',
                'NO ACTION'
            );
        }
    }

    


    public function safeDown()
    {
        foreach (self::$columns as $column => $table) {
            $this->dropForeignKey(
                '{{%fk-docs_concession-' . $column . '}}',
                '{{%dictionary_available_document_types_for_concession}}'
            );

            $this->dropIndex(
                '{{%idx-docs_concession-' . $column . '}}',
                '{{%dictionary_available_document_types_for_concession}}'
            );

            $this->dropColumn('{{%dictionary_available_document_types_for_concession}}', $column);
        }
        
        $this->dropColumn('{{%dictionary_available_document_types_for_concession}}', 'document_set_code');
    }
}
