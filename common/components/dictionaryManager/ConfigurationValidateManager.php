<?php

namespace common\components\dictionaryManager;

use Yii;

class ConfigurationValidateManager
{
    






    private static function suspendUnspecifiedCodesError(bool $state): void
    {
        Yii::$app->configurationManager->suspendUnspecifiedCodesError($state);
    }

    




    public static function enableUnspecifiedCodesError(): void
    {
        ConfigurationValidateManager::suspendUnspecifiedCodesError(true);
    }

    




    public static function disableUnspecifiedCodesError(): void
    {
        ConfigurationValidateManager::suspendUnspecifiedCodesError(false);
    }
}
