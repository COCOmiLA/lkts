<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200814_110137_insert_education_from_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {

        $code = new CodeSetting();
        $code->description = 'Код очной формы обучения';
        $code->name = 'full_time_education_form_code';
        $code->value = '000000001';
        $code->save();
        $code_part = new CodeSetting();
        $code_part->description = 'Код очно-заочной формы обучения';
        $code_part->name = 'part_time_education_form_code';
        $code_part->value = '000000002';
        $code_part->save();
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'full_time_education_form_code',
        ]);
        if ($code != null) {
            $code->delete();
        }
        $code = CodeSetting::findOne([
            'name' => 'part_time_education_form_code',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }

    













}
