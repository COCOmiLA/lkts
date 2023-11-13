<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160616_140240_add_spec_category_and_readonly extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'category_code', $this->string(100));
        $this->addColumn('{{%bachelor_speciality}}', 'category_name', $this->string(1000));
        $this->addColumn('{{%bachelor_speciality}}', 'readonly', $this->smallInteger()->defaultValue(0));
        
        Yii::$app->db->schema->refresh(); 
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_speciality}}', 'category_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'category_name');
        $this->dropColumn('{{%bachelor_speciality}}', 'readonly');
        
        Yii::$app->db->schema->refresh(); 
    }
}
