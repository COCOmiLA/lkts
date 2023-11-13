<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210723_115935_add_new_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $codeSettingNew = new CodeSetting();
        $codeSettingNew->attributes = [
            'description' => 'Код документа согласия на зачисления',
            'name' => 'agreement_document_type_guid',
            'value' => ''
        ];
        $codeSettingNew->save();
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'agreement_document_type_guid',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }
}
