<?php

namespace common\components\soapResponse\exceptions;

use common\components\soapResponse\interfaces\ISoapResponse;
use Throwable;






class SoapBadRequestException extends \Exception
{
    public function __construct(ISoapResponse $response, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->message = "Ошибка обращения к методу \"{$response->getMethodName()}\". " . $this->message;
    }
}