<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m201022_081418_add_block_all_application_after_first_approvment extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $setting = new CodeSetting();
        $setting->value = 0;
        $setting->name = 'block_application_after_approve';
        $setting->description = 'Блокировать редактирование заявления (данные об образовании, редактирование льгот, преимущественных прав, целевых, редактирование индивидуальных достижений) после первого одобрения.';
        $setting->save();
    }

    


    public function safeDown()
    {
        $setting = CodeSetting::findOne([
            'name' => 'block_application_after_approve'
        ]);
        if($setting !== null) {
            $setting->delete();
        }

    }

    













}
