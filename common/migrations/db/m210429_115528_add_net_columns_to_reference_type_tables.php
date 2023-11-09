<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210429_115528_add_net_columns_to_reference_type_tables extends MigrationWithDefaultOptions
{
    private $tables_list = [
        'discipline_reference_type',
        'achievement_category_reference_type',
        'achievement_curriculum_reference_type',
        'admission_campaign_reference_type',
        'available_document_type_filter_reference_type',
        'budget_level_reference_type',
        'competitive_group_reference_type',
        'curriculum_reference_type',
        'detail_group_reference_type',
        'direction_reference_type',
        'discipline_form_reference_type',
        'document_set_reference_type',
        'education_form_reference_type',
        'education_level_reference_type',
        'education_program_reference_type',
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
    ];

    


    public function safeUp()
    {
        foreach ($this->tables_list as $table_name) {
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('reference_parent_uid')) {
                $this->addColumn('{{%' . $table_name . '}}', 'reference_parent_uid', $this->string());
            }
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('reference_data_version')) {
                $this->addColumn('{{%' . $table_name . '}}', 'reference_data_version', $this->string());
            }
        }
    }

    


    public function safeDown()
    {
        foreach ($this->tables_list as $table_name) {
            if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('reference_parent_uid')) {
                $this->dropColumn('{{%' . $table_name . '}}', 'reference_parent_uid');
            }
            if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('reference_data_version')) {
                $this->dropColumn('{{%' . $table_name . '}}', 'reference_data_version');
            }
        }
    }

}
