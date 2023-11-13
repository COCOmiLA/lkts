<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\DocumentType;
use common\models\settings\CodeSetting;




class m220117_094726_remove_unused_codes extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $this->deleteCode('edu_docs_code');
        $this->deleteCode('agreement_document_type');
        $this->deleteCode('passport_code');
    }

    


    public function safeDown()
    {
        
        $code = CodeSetting::findOne(['name' => 'edu_docs_code']);
        if (!$code) {
            $code = new CodeSetting();
            $code->description ='Код категории "Документы об образовании"';
            $code->name ='edu_docs_code';
            $code->value = '';
            if($code->validate()){
                $code->save();
            }
        }
        
        
        $code = CodeSetting::findOne(['name' => 'agreement_document_type']);
        if (!$code) {
            $code = new CodeSetting();
            $code->description ='Код документа согласия на зачисления';
            $code->name ='agreement_document_type';
            $code->value = '';
            if($code->validate()){
                $code->save();
            }
        }
        
        
        $code = CodeSetting::findOne(['name' => 'passport_code']);
        if (!$code) {
            try {
                $uid = \Yii::$app->configurationManager->getCode('russian_passport_guid');
                $passport = DocumentType::findByUID($uid);


                $code = new CodeSetting();
                $code->description ='Код документа, удостоверяющего личность по умолчанию (Паспорт РФ)';
                $code->name = 'passport_code';
                $code->value = $passport->code;
                if($code->validate()){
                    $code->save();
                }
            } catch (\Exception $e) {
                \Yii::error('Не удалось восстановить значение кода passport_code: ' . $e->getMessage());
            }
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
