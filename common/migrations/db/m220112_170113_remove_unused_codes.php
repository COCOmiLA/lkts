<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m220112_170113_remove_unused_codes extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->deleteCode('discipline_form_ege');
        $this->deleteCode('individual_achievement_code');
    }

    


    public function safeDown()
    {
        
        $ege_code = '';
        $code = new CodeSetting();
        $code->attributes = [
            'description' =>'Код типа экзамена ЕГЭ',
            'name' => 'discipline_form_ege',
            'value' => $ege_code
        ];
        if($code->validate()){
            $code->save();
        }
        
        
        $code = new CodeSetting();
        $code->description ='Код элемента "Индивидуальное Достижение" из справочника "Дисциплины"';
        $code->name ='individual_achievement_code';
        $code->value = '';

        if($code->validate()){
            $code->save();
        }
    }
    
    protected function deleteCode($name)
    {
        $code = CodeSetting::findOne([
            'name' => $name,
        ]);
        if($code != null) {
            $code->delete();
        }
    }
}
