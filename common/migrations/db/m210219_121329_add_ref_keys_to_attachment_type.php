<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210219_121329_add_ref_keys_to_attachment_type extends MigrationWithDefaultOptions
{
    private static $columns =  [
        'admission_campaign_ref_id' => 'admission_campaign_reference_type',
        'document_set_ref_id' => 'document_set_reference_type',
        'document_type_id' => 'dictionary_document_type',
    ];

    


    public function safeUp()
    {
        $this->addColumn('{{%attachment_type}}', 'document_set_code', $this->string());
        
        foreach (self::$columns as $column => $table) {
            $this->addColumn('{{%attachment_type}}', $column, $this->integer());
            
            $this->createIndex(
                '{{%idx-attachment_type-' . $column . '}}',
                '{{%attachment_type}}',
                $column
            );

            $this->addForeignKey(
                '{{%fk-attachment_type-' . $column . '}}',
                '{{%attachment_type}}',
                $column,
                '{{%' . $table . '}}',
                'id',
                'NO ACTION'
            );
        }
    }

    


    public function safeDown()
    {
        $this->dropColumn('{{%attachment_type}}', 'document_set_code');
        
        foreach (self::$columns as $column => $table) {
            $this->dropForeignKey(
                '{{%fk-attachment_type-' . $column . '}}',
                '{{%attachment_type}}'
            );

            $this->dropIndex(
                '{{%idx-attachment_type-' . $column . '}}',
                '{{%attachment_type}}'
            );

            $this->dropColumn('{{%attachment_type}}', $column);
        }
    }
}
