<?php

namespace console\components;

use Yii;
use yii\console\ErrorHandler as ConsoleErrorHandler;

class ErrorHandler extends ConsoleErrorHandler
{
    protected function renderException($exception)
    {
        Yii::$app->supportInfo->print();
        parent::renderException($exception);
    }
}