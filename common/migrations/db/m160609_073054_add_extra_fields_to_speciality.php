<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160609_073054_add_extra_fields_to_speciality extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->dropColumn('{{%exam_register}}', 'consult_date_id');
        $this->addColumn('{{%dictionary_speciality}}', 'detail_group_name', $this->string(1000));
        $this->addColumn('{{%dictionary_speciality}}', 'detail_group_code', $this->string(100));
        
        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%dictionary_speciality}}', 'detail_group_name');
        $this->dropColumn('{{%dictionary_speciality}}', 'detail_group_code');
        
        Yii::$app->db->schema->refresh();
    }
}
