<?php

namespace common\components\EnvironmentManager\exceptions;

use Throwable;
use Yii;

class UnsupportedDBMSException extends EnvironmentException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct($code, $previous);
        $driver = Yii::$app->db->driverName;
        $serverName = Yii::$app->db->serverVersion;
        $this->message = parent::getMessage() . "<br><strong>Используется не поддерживаемая версия СУБД({$driver}({$serverName}))</strong>";
    }
}