<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210218_085212_add_ref_keys_to_dictionary_admission_procedures_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'admission_campaign_ref_id' => 'admission_campaign_reference_type',
            'education_source_ref_id' => 'education_source_reference_type',
            'admission_category_id' => 'dictionary_admission_categories',
            'privilege_id' => 'dictionary_privileges',
            'special_mark_id' => 'dictionary_special_marks',
        ];
        
        foreach ($columns as $column => $table) {
            $this->addColumn('{{%dictionary_admission_procedure}}', $column, $this->integer());
            
            $this->createIndex(
                '{{%idx-dictionary_admission_procedure-' . $column . '}}',
                '{{%dictionary_admission_procedure}}',
                $column
            );

            $this->addForeignKey(
                '{{%fk-dictionary_admission_procedure-' . $column . '}}',
                '{{%dictionary_admission_procedure}}',
                $column,
                '{{%' . $table . '}}',
                'id',
                'NO ACTION'
            );
        }
    }

    


    public function safeDown()
    {
        $columns = [
            'admission_campaign_ref_id' => 'admission_campaign_reference_type',
            'education_source_ref_id' => 'education_source_reference_type',
            'admission_category_id' => 'dictionary_admission_categories',
            'privilege_id' => 'dictionary_privileges',
            'special_mark_id' => 'dictionary_special_marks',
        ];
        
        foreach ($columns as $column => $table) {
            $this->dropForeignKey(
                '{{%fk-dictionary_admission_procedure-' . $column . '}}',
                '{{%dictionary_admission_procedure}}'
            );

            $this->dropIndex(
                '{{%idx-dictionary_admission_procedure-' . $column . '}}',
                '{{%dictionary_admission_procedure}}'
            );

            $this->dropColumn('{{%dictionary_admission_procedure}}', $column);
        }
    }
}
