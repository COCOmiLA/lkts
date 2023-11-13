<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210902_145135_add_new_ref_type_fields extends MigrationWithDefaultOptions
{
    private $tables_list = [
        'reference_type',
        'achievement_category_reference_type',
        'achievement_curriculum_reference_type',
        'achievement_document_type_reference_type',
        'admission_campaign_reference_type',
        'available_document_type_filter_reference_type',
        'budget_level_reference_type',
        'campaign_info_reference_type',
        'competitive_group_reference_type',
        'curriculum_reference_type',
        'detail_group_reference_type',
        'direction_reference_type',
        'discipline_form_reference_type',
        'discipline_reference_type',
        'document_set_reference_type',
        'education_form_reference_type',
        'education_level_reference_type',
        'education_program_reference_type',
        'education_reference_type',
        'education_source_reference_type',
        'olympic_class_reference_type',
        'olympic_kind_reference_type',
        'olympic_level_reference_type',
        'olympic_profile_reference_type',
        'olympic_reference_type',
        'olympic_type_reference_type',
        'profile_reference_type',
        'subdivision_reference_type',
        'subject_set_reference_type',
        'user_reference_type',
        'variant_of_retest_reference_type',
        'special_requirements',

        'dictionary_education_type',
        'dictionary_admission_categories',
        'dictionary_country',
        'dictionary_document_type',
        'dictionary_foreign_languages',
        'dictionary_gender',
        'dictionary_privileges',
        'dictionary_special_marks',
    ];

    


    public function safeUp()
    {
        foreach ($this->tables_list as $table_name) {
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('is_folder')) {
                $this->addColumn('{{%' . $table_name . '}}', 'is_folder', $this->boolean()->defaultValue(false));
            }
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('has_deletion_mark')) {
                $this->addColumn('{{%' . $table_name . '}}', 'has_deletion_mark', $this->boolean()->defaultValue(false));
            }
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('posted')) {
                $this->addColumn('{{%' . $table_name . '}}', 'posted', $this->boolean()->defaultValue(false));
            }
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('is_predefined')) {
                $this->addColumn('{{%' . $table_name . '}}', 'is_predefined', $this->boolean()->defaultValue(false));
            }
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('predefined_data_name')) {
                $this->addColumn('{{%' . $table_name . '}}', 'predefined_data_name', $this->string(1000));
            }
        }
        \Yii::$app->db->schema->refresh();
    }

    


    public function safeDown()
    {
        foreach ($this->tables_list as $table_name) {
            if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('is_folder')) {
                $this->dropColumn('{{%' . $table_name . '}}', 'is_folder');
            }
            if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('has_deletion_mark')) {
                $this->dropColumn('{{%' . $table_name . '}}', 'has_deletion_mark');
            }
            if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('posted')) {
                $this->dropColumn('{{%' . $table_name . '}}', 'posted');
            }
            if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('is_predefined')) {
                $this->dropColumn('{{%' . $table_name . '}}', 'is_predefined');
            }
            if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('predefined_data_name')) {
                $this->dropColumn('{{%' . $table_name . '}}', 'predefined_data_name');
            }
        }
    }

}
