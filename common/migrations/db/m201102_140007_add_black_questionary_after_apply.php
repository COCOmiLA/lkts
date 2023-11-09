<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m201102_140007_add_black_questionary_after_apply extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

        $setting = new CodeSetting();
        $setting->value = 0;
        $setting->name = 'block_questionary_after_approve';
        $setting->description = 'Блокировать редактирование анкеты (за исключением ФИО и паспортных данных) после первого одобрения.';
        $setting->save();
    }

    


    public function safeDown()
    {
        $setting = CodeSetting::findOne([
            'name' => 'block_questionary_after_approve'
        ]);
        if($setting !== null) {
            $setting->delete();
        }
    }

    













}
