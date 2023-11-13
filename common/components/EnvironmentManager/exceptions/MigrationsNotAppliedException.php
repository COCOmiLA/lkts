<?php

namespace common\components\EnvironmentManager\exceptions;

class MigrationsNotAppliedException extends EnvironmentException
{
    public function __construct($message, $code = 0, \Throwable $previous = null) {
        parent::__construct($code, $previous);
        $this->message = $message;
    }
}
