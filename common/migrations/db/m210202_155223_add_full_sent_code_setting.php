<?php

use common\components\Migration\MigrationWithDefaultOptions;
use common\models\settings\CodeSetting;




class m210202_155223_add_full_sent_code_setting extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = new CodeSetting();
        $code->attributes = [
            'description' => 'Способ синхронизации заявления с 1С (отправка/получение)',
            'name' => 'app_sending_type',
            'value' => \common\components\ApplicationSendHandler\BaseApplicationSendHandler::APP_SEND_TYPE_BY_STEPS
        ];
        $code->save();
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
