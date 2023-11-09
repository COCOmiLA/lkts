<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\DocumentType;
use common\models\settings\CodeSetting;




class m200624_140643_insert_def_target_reception_type_to_code_settings extends MigrationWithDefaultOptions
{
    


    public function up()
    {
        $docType = DocumentType::findOne(['description' => 'Целевое направление', 'archive' => false]);
        $code = new CodeSetting();
        $code->attributes = [
            'description' =>'Код типа документа по умолчанию для подтверждающего документа целевого приема',
            'name' => 'target_reception_document_type',
        ];
        if($docType != null) {
            $code->value = $docType->code;
        } else {
            $code->value = '';
        }
        if($code->validate()){
            $code->save();
        }
    }

    


    public function down()
    {
        $code = CodeSetting::findOne([
            'name' => 'target_reception_document_type',
        ]);
        if($code != null) {
            $code->delete();
        }
    }

    













}
