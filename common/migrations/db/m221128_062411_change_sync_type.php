<?php

use common\components\ApplicationSendHandler\BaseApplicationSendHandler;
use common\components\Migration\MigrationWithDefaultOptions;




class m221128_062411_change_sync_type extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $code = \common\models\settings\CodeSetting::findOne(['name' => 'app_sending_type']);
        if ($code) {
            $code->value = BaseApplicationSendHandler::APP_SEND_TYPE_FULL;
            $code->save(false);
        }
    }
}
