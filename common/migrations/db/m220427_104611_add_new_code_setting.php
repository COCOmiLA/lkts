<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m220427_104611_add_new_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->description = 'Разрешать возврат к модерации принятых заявлений';
        $code->name = 'allow_return_approved_application_to_sent';
        $code->value = 0;

        $code->save(false);
    }

    


    public function safeDown()
    {
        $code = CodeSetting::findOne([
            'name' => 'allow_return_approved_application_to_sent',
        ]);
        if ($code != null) {
            $code->delete();
        }
    }
}
