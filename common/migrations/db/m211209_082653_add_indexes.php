<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m211209_082653_add_indexes extends MigrationWithDefaultOptions
{
    private $tables = [
        '{{%admission_campaign}}',
        '{{%campaign_info}}',
        '{{%dictionary_admission_base}}',
        '{{%dictionary_education_type}}',
        '{{%dictionary_admission_features}}',
        '{{%dictionary_admission_procedure}}',
        '{{%dictionary_admission_categories}}',
        '{{%dictionary_reasons_for_exam}}',
        '{{%dictionary_country}}',
        '{{%dictionary_individual_achievement}}',
        '{{%individual_achievements_document_types}}',
        '{{%dictionary_family_type}}',
        '{{%dictionary_document_type}}',
        '{{%dictionary_foreign_languages}}',
        '{{%dictionary_gender}}',
        '{{%dictionary_privileges}}',
        '{{%dictionary_special_marks}}',
        '{{%special_requirements}}',

        '{{%cget_entrance_test_set}}',
        '{{%cget_entrance_test}}',
        '{{%cget_child_subject}}',


        '{{%variant_of_retest_reference_type}}',
        '{{%achievement_category_reference_type}}',
        '{{%achievement_curriculum_reference_type}}',
        '{{%admission_campaign_reference_type}}',
        '{{%available_document_type_filter_reference_type}}',
        '{{%budget_level_reference_type}}',
        '{{%competitive_group_reference_type}}',
        '{{%curriculum_reference_type}}',
        '{{%detail_group_reference_type}}',
        '{{%direction_reference_type}}',
        '{{%discipline_form_reference_type}}',
        '{{%discipline_reference_type}}',
        '{{%document_set_reference_type}}',
        '{{%education_form_reference_type}}',
        '{{%education_level_reference_type}}',
        '{{%education_program_reference_type}}',
        '{{%education_source_reference_type}}',
        '{{%olympic_class_reference_type}}',
        '{{%olympic_kind_reference_type}}',
        '{{%olympic_level_reference_type}}',
        '{{%olympic_profile_reference_type}}',
        '{{%olympic_reference_type}}',
        '{{%olympic_type_reference_type}}',
        '{{%profile_reference_type}}',
        '{{%subdivision_reference_type}}',
        '{{%user_reference_type}}',
    ];

    private function clearTable(string $name): string
    {
        $name = str_replace('{{%', '', $name);
        return str_replace('}}', '', $name);
    }

    private function getIndexName(string $table_name): string
    {
        return 'idx_' . $this->clearTable($table_name) . '_archive';
    }

    


    public function up()
    {
        foreach ($this->tables as $table) {
            $table_schema = Yii::$app->db->schema->getTableSchema($table);
            if (isset($table_schema->columns['archive'])) {
                try {
                    $this->createIndex($this->getIndexName($table), $table, 'archive');

                } catch (\Throwable $ex) {
                }
            }
        }
    }

    


    public function down()
    {
        foreach ($this->tables as $table) {
            try {
                $this->dropIndex($this->getIndexName($table), $table);
            } catch (\Throwable $e) {
                
            }
        }
    }
}
