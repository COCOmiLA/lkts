<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m200818_213401_insert_agreemeent_document_type_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->description = 'Код документа согласия на зачисления';
        $code->name ='agreement_document_type';
        $docType = \common\models\dictionary\DocumentType::findOne([
           'description' => 'Согласие на зачисление'
        ]);
        $code->value = $docType !== null ? $docType->code : '';

        if($code->validate()){
            $code->save();
        }
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'agreement_document_type',
        ]);
        if($code != null) {
            $code->delete();
        }
    }

    













}
