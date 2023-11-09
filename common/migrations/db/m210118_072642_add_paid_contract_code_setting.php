<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210118_072642_add_paid_contract_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->description = 'Код документа договора об оказании платных образовательных услуг';
        $code->name = 'paid_contract_document_type';
        $code->value = '';
        $code->save();
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'paid_contract_document_type',
        ]);
        if (!empty($code)) {
            $code->delete();
        }
    }

}
