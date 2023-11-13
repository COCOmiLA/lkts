<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200624_132510_insert_not_found_in_classifier_code_to_code_settings_table extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $code = new CodeSetting();
        $code->attributes = [
            'description' =>'Скрывать опцию "Не нашел свой адрес в классификаторе"',
            'name' => 'display_not_found_in_classifier',
            'value' => '0'
        ];
        if($code->validate()){
            $code->save();
        }
    }

    


    public function down()
    {
        $code = CodeSetting::findOne([
            'name' => 'display_not_found_in_classifier',
        ]);
        if($code != null) {
            $code->delete();
        }
    }

    













}
