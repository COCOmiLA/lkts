<?php

use common\components\Migration\MigrationWithDefaultOptions;




class m210517_103412_add_additional_cols_to_education_ref extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $table_name = 'education_reference_type';
        if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('reference_parent_uid')) {
            $this->addColumn('{{%' . $table_name . '}}', 'reference_parent_uid', $this->string());
        }
        if (!Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('reference_data_version')) {
            $this->addColumn('{{%' . $table_name . '}}', 'reference_data_version', $this->string());
        }
    }

    


    public function safeDown()
    {
        $table_name = 'education_reference_type';
        if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('reference_parent_uid')) {
            $this->dropColumn('{{%' . $table_name . '}}', 'reference_parent_uid');
        }
        if (Yii::$app->db->schema->getTableSchema('{{%' . $table_name . '}}')->getColumn('reference_data_version')) {
            $this->dropColumn('{{%' . $table_name . '}}', 'reference_data_version');
        }
    }

}
