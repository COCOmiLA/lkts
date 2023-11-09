<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200624_123109_insert_dob_code_setting extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $code = new CodeSetting();
        $code->attributes = [
            'description' => 'Минимальный возраст поступающего при регистрации',
            'name' => 'min_age',
            'value' => 0
        ];
        $code->save();
    }

    


    public function down()
    {
        $code = CodeSetting::findOne([
            'name' => 'min_age',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }

    













}
