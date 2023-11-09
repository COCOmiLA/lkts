<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\dictionary\DocumentType;
use common\models\settings\CodeSetting;
use yii\helpers\VarDumper;




class m220429_101156_add_code_setting_app_doc_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $document_type = DocumentType::findOne([
            'description' => 'Заявление',
            'is_predefined' => true
        ]);
        
        $code = new CodeSetting();
        $code->description = 'Код типа документа "Заявление"';
        $code->name = 'application_document_type_guid';
        $code->value = $document_type->ref_key ?? '';
        
        if (!$code->save(true, ['value', 'description', 'name']))  {
            \Yii::error('Не удалось сохранить код по умолчанию ' . PHP_EOL . VarDumper::dumpAsString($code->errors), 'CODE_SETTINGS');
        }
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne(['name' => 'application_document_type_guid']);
        if ($code != null) {
            $code->delete();
        }
    }
}
