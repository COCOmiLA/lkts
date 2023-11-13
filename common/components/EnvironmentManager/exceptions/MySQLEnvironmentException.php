<?php
namespace common\components\EnvironmentManager\exceptions;


use Throwable;

class MySQLEnvironmentException extends EnvironmentException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct($code, $previous);
        $connection = \Yii::$app->db->getServerVersion();
        $this->message = parent::getMessage() . "<br><strong>Рекомендуемая версия MYSQL для стабальной работы портала \"5.7\" (сейчас используется {$connection})</strong>";
    }
}