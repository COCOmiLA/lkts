<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m201230_092551_add_abiturient_avarar_required_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $setting = new CodeSetting();
        $setting->value = 0;
        $setting->name = 'required_abiturient_avatar';
        $setting->description = 'Требовать обязательное заполнение поля "Фото" поступающим.';
        $setting->save();
    }

    


    public function safeDown()
    {
        $setting = CodeSetting::findOne([
            'name' => 'required_abiturient_avatar'
        ]);
        if($setting !== null) {
            $setting->delete();
        }
    }

    













}
