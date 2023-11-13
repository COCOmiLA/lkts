<?php
namespace common\components\EntrantModeratorManager\exceptions;


use Throwable;

class EntrantManagerValidationException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Ошибка сохранения модератора в системе. ' . $message, $code, $previous);
    }
}