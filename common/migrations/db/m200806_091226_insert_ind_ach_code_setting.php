<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200806_091226_insert_ind_ach_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->description ='Код элемента "Индивидуальное Достижение" из справочника "Дисциплины"';
        $code->name ='individual_achievement_code';
        $code->value = '';

        if($code->validate()){
            $code->save();
        }

    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'individual_achievement_code',
        ]);
        if($code != null) {
            $code->delete();
        }

    }

    













}
