<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210225_170333_add_ref_keys_to_individual_achievement_document_type extends MigrationWithDefaultOptions
{
    private static $columns =  [
        'admission_campaign_ref_id' => 'admission_campaign_reference_type',
        'available_document_type_filter_ref_id' => 'available_document_type_filter_reference_type',
        'document_set_ref_id' => 'document_set_reference_type',
    ];
    
    


    public function safeUp()
    {
        $this->addColumn('{{%individual_achievements_document_types}}', 'document_set_code', $this->string());
        
        foreach (self::$columns as $column => $table) {
            $this->addColumn('{{%individual_achievements_document_types}}', $column, $this->integer());
            
            $this->createIndex(
                '{{%idx-ind_ach_document_types-' . $column . '}}',
                '{{%individual_achievements_document_types}}',
                $column
            );

            $this->addForeignKey(
                '{{%fk-ind_ach_document_types-' . $column . '}}',
                '{{%individual_achievements_document_types}}',
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
                '{{%fk-ind_ach_document_types-' . $column . '}}',
                '{{%individual_achievements_document_types}}'
            );

            $this->dropIndex(
                '{{%idx-ind_ach_document_types-' . $column . '}}',
                '{{%individual_achievements_document_types}}'
            );

            $this->dropColumn('{{%individual_achievements_document_types}}', $column);
        }
        
        $this->dropColumn('{{%individual_achievements_document_types}}', 'document_set_code');
    }
}
