<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210429_142934_alter_odata_models extends MigrationWithDefaultOptions
{
    private $tables_to_update_data_version = [
        'dictionary_education_type',
        'dictionary_admission_categories',
        'dictionary_country',
        'dictionary_document_type',
        'dictionary_foreign_languages',
        'dictionary_gender',
        'dictionary_privileges',
        'dictionary_special_marks',
    ];
    private $tables_to_add_parent_key = [
        'dictionary_admission_categories',
        'dictionary_country',
        'dictionary_gender',
    ];

    


    public function safeUp()
    {
        foreach ($this->tables_to_update_data_version as $table_with_data_version) {
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_with_data_version . '}}')->getColumn('data_version')) {
                $this->addColumn('{{%' . $table_with_data_version . '}}', 'data_version', $this->string());
            } else {
                $this->alterColumn('{{%' . $table_with_data_version . '}}', 'data_version', $this->string());
            }
        }
        foreach ($this->tables_to_add_parent_key as $table_with_parent_key) {
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_with_parent_key . '}}')->getColumn('parent_key')) {
                $this->addColumn('{{%' . $table_with_parent_key . '}}', 'parent_key', $this->string());
            } else {
                $this->alterColumn('{{%' . $table_with_parent_key . '}}', 'parent_key', $this->string());
            }
        }
    }

    


    public function safeDown()
    {
        foreach ($this->tables_to_add_parent_key as $table_with_parent_key) {
            if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_with_parent_key . '}}')->getColumn('parent_key')) {
                $this->dropColumn('{{%' . $table_with_parent_key . '}}', 'parent_key');
            }
        }
    }

}
