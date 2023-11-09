<?php

use common\components\Migration\MigrationWithDefaultOptions;







class m210121_030051_add_ref_columns_to_dictionary_ege_discipline_table extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'campaign_ref_id' => 'admission_campaign',
            'subdivision_ref_id' => 'subdivision',
            'direction_ref_id' => 'direction',
            'profile_ref_id' => 'profile',
            'education_level_ref_id' => 'education_level',
            'education_form_ref_id' => 'education_form',
            'education_program_ref_id' => 'education_program',
            'competitive_group_ref_id' => 'competitive_group',
            'subjects_set_ref_id' => 'subject_set',
            'subject_ref_id',
            'child_subject_ref_id',
            'alternate_subject_ref_id',
            'entrance_test_result_source_ref_id' => 'discipline_form',
        ];

        foreach ($columns as $column => $table) {
            if(is_integer($column)) {
                $column = $table;
                $table = 'discipline_reference_type';
            } else {
                $table .= '_reference_type';
            }


            $this->addColumn('{{%dictionary_ege_discipline}}', $column, $this->integer()->null());

            
            $this->createIndex(
                '{{%idx-dictionary_ege_discipline-'.$column.'}}',
                '{{%dictionary_ege_discipline}}',
                $column
            );

            
            $this->addForeignKey(
                '{{%fk-dictionary_ege_discipline-'.$column.'}}',
                '{{%dictionary_ege_discipline}}',
                $column,
                '{{%'.$table.'}}',
                'id',
                'NO ACTION'
            );
        }

    }

    


    public function safeDown()
    {
        $columns = [
            'campaign_ref_id',
            'subdivision_ref_id',
            'direction_ref_id',
            'profile_ref_id',
            'education_level_ref_id',
            'education_form_ref_id',
            'education_program_ref_id',
            'competitive_group_ref_id',
            'subjects_set_ref_id',
            'subject_ref_id',
            'alternate_subject_ref_id',
            'child_subject_ref_id',
            'entrance_test_result_source_ref_id',
        ];

        foreach ($columns as $column) {
            
            $this->dropForeignKey(
                '{{%fk-dictionary_ege_discipline-'.$column.'}}',
                '{{%dictionary_ege_discipline}}'
            );

            
            $this->dropIndex(
                '{{%idx-dictionary_ege_discipline-'.$column.'}}',
                '{{%dictionary_ege_discipline}}'
            );

            $this->dropColumn('{{%dictionary_ege_discipline}}', $column);
        }
    }
}
