<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200624_093915_insert_default_code_discipline_form extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $ege_code = '';
        $code = new CodeSetting();
        $code->attributes = [
            'description' => 'Код типа экзамена ЕГЭ',
            'name' => 'discipline_form_ege',
            'value' => $ege_code
        ];
        $code->save();
    }

    


    public function down()
    {
        $code = CodeSetting::findOne([
            'name' => 'discipline_form_ege',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }

    













}
