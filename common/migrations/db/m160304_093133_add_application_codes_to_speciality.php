<?php

use common\components\Migration\MigrationWithDefaultOptions;

class m160304_093133_add_application_codes_to_speciality extends MigrationWithDefaultOptions
{
    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'faculty_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'speciality_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'educationlevel_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'educationform_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'finance_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'profil_1c_code', $this->string("100"));
        $this->addColumn('{{%bachelor_speciality}}', 'eduprogram_1c_code', $this->string("100"));

        Yii::$app->db->schema->refresh();
    }

    public function safeDown()
    {
        $this->dropColumn('{{%bachelor_speciality}}', 'faculty_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'speciality_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'educationlevel_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'educationform_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'finance_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'profil_1c_code');
        $this->dropColumn('{{%bachelor_speciality}}', 'eduprogram_1c_code');
        
        Yii::$app->db->schema->refresh();
    }
}
