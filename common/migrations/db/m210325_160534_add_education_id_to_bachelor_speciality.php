<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210325_160534_add_education_id_to_bachelor_speciality extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->addColumn('{{%bachelor_speciality}}', 'education_id', $this->integer());
        $this->addColumn('{{%education_data}}', 'archive', $this->boolean()->defaultValue(false));

        $this->createIndex(
            '{{%idx-bachelor_speciality-education_id}}',
            '{{%bachelor_speciality}}',
            'education_id'
        );

        $this->addForeignKey(
            '{{%fk-bachelor_speciality-education_id}}',
            '{{%bachelor_speciality}}',
            'education_id',
            '{{%education_data}}',
            'id',
            'SET NULL'
        );


        $setting = new CodeSetting();
        $setting->value = 0;
        $setting->name = 'allow_multiple_educations';
        $setting->description = 'Сможет ли поступающий указывать несколько документов об образовании для каждого выбранного направления подготовки';
        $setting->save();
    }

    


    public function safeDown()
    {
        $setting = CodeSetting::findOne(['name' => 'allow_multiple_educations']);
        if(!empty($setting)) {
            $setting->delete();
        }

        $this->dropForeignKey(
            '{{%fk-bachelor_speciality-education_id}}',
            '{{%bachelor_speciality}}'
        );

        $this->dropIndex(
            '{{%idx-bachelor_speciality-education_id}}',
            '{{%bachelor_speciality}}'
        );

        $this->dropColumn('{{%bachelor_speciality}}', 'education_id');
        $this->dropColumn('{{%education_data}}', 'archive');
    }

}
