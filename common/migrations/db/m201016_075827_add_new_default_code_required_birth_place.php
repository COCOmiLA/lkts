<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m201016_075827_add_new_default_code_required_birth_place extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $setting = new CodeSetting();
        $setting->value = 0;
        $setting->name = 'required_birth_place';
        $setting->description = 'Требовать обязательное заполнение поля "Место рождения" поступающим.';
        $setting->save();
    }

    


    public function safeDown()
    {
        $set = CodeSetting::findOne(['name' => 'required_birth_place']);
        if($set !== null) {
            $set->delete();
        }
    }

    













}
