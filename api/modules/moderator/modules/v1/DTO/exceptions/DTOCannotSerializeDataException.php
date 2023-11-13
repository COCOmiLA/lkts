<?php
namespace api\modules\moderator\modules\v1\DTO\exceptions;


use Exception;
use Throwable;

class DTOCannotSerializeDataException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("Ошибка сериализации XML данных. " . $message, $code, $previous);
    }
}