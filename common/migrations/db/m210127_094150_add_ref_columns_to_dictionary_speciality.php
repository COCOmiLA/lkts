<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210127_094150_add_ref_columns_to_dictionary_speciality extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $columns = [
            'competitive_group_ref_id' => 'competitive_group',
            'subdivision_ref_id' => 'subdivision',
            'direction_ref_id' => 'direction',
            'profile_ref_id' => 'profile',
            'education_level_ref_id' => 'education_level',
            'education_form_ref_id' => 'education_form',
            'education_program_ref_id' => 'education_program',
            'education_source_ref_id' => 'education_source',
            'budget_level_ref_id' => 'budget_level',
            'detail_group_ref_id' => 'detail_group',
            'campaign_ref_id' => 'admission_campaign',
        ];

        foreach ($columns as $column => $table) {
            $table .= '_reference_type';


            $this->addColumn('{{%dictionary_speciality}}', $column, $this->integer()->null());

            $this->createIndex(
                '{{%idx-dictionary_speciality-' . $column . '}}',
                '{{%dictionary_speciality}}',
                $column
            );

            $this->addForeignKey(
                '{{%fk-dictionary_speciality-' . $column . '}}',
                '{{%dictionary_speciality}}',
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
            'competitive_group_ref_id' => 'competitive_group',
            'subdivision_ref_id' => 'subdivision',
            'direction_ref_id' => 'direction',
            'profile_ref_id' => 'profile',
            'education_level_ref_id' => 'education_level',
            'education_form_ref_id' => 'education_form',
            'education_program_ref_id' => 'education_program',
            'education_source_ref_id' => 'education_source',
            'budget_level_ref_id' => 'budget_level',
            'detail_group_ref_id' => 'detail_group',
            'campaign_ref_id' => 'admission_campaign',
        ];

        foreach ($columns as $column => $_) {
            $this->dropForeignKey(
                '{{%fk-dictionary_speciality-' . $column . '}}',
                '{{%dictionary_speciality}}'
            );

            $this->dropIndex(
                '{{%idx-dictionary_speciality-' . $column . '}}',
                '{{%dictionary_speciality}}'
            );

            $this->dropColumn('{{%dictionary_speciality}}', $column);
        }
    }

}
