<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210521_063452_remove_redundant_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $setting = CodeSetting::findOne(['name' => 'allow_multiple_educations']);
        if (!empty($setting)) {
            $setting->delete();
        }
    }

    


    public function safeDown()
    {
        $setting = CodeSetting::findOne(['name' => 'allow_multiple_educations']);
        if (empty($setting)) {
            $setting = new CodeSetting();
            $setting->value = 0;
            $setting->name = 'allow_multiple_educations';
            $setting->description = 'Сможет ли поступающий указывать несколько документов об образовании для каждого выбранного направления подготовки';
            $setting->save();
        }
    }

}
