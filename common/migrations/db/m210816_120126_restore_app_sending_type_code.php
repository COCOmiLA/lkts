<?php

use common\components\ApplicationSendHandler\BaseApplicationSendHandler;
use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210816_120126_restore_app_sending_type_code extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = CodeSetting::findOne([
            'name' => 'app_sending_type',
        ]);
        if (!$code) {
            $code = new CodeSetting();
            $code->attributes = [
                'description' => 'Способ синхронизации заявления с 1С (отправка/получение)',
                'name' => 'app_sending_type',
            ];
        }
        $code->value = BaseApplicationSendHandler::APP_SEND_TYPE_BY_STEPS;
        $code->save(false);
    }
}
