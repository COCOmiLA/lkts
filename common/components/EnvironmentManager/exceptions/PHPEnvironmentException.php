<?php

namespace common\components\EnvironmentManager\exceptions;


use Throwable;

class PHPEnvironmentException extends EnvironmentException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct($code, $previous);
        $php_ver = phpversion();
        $this->message = parent::getMessage() . "<br><strong>Рекомендуемая версия PHP для стабальной работы портала \"7.4\" (сейчас используется {$php_ver})</strong>";
    }

}