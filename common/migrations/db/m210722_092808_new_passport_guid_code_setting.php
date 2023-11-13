<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210722_092808_new_passport_guid_code_setting extends MigrationWithDefaultOptions
{

    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->attributes = [
            'description' => 'Код документа, удостоверяющего личность по умолчанию (Паспорт РФ)',
            'name' => 'russian_passport_guid',
            'value' => ''
        ];
        $code->save();

        $codeOld = CodeSetting::findOne([
            'name' => 'passport_code',
        ]);
        if (!is_null($codeOld)) {
            $codeOld->description = 'Код документа, удостоверяющего личность по умолчанию (Паспорт РФ) (Архивная)';
            $codeOld->save(false, ['description']);
        }
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'app_sending_type',
        ]);
        if (!is_null($code)) {
            $code->delete();
        }
    }
}
