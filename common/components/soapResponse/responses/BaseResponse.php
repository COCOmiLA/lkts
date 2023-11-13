<?php


namespace common\components\soapResponse\responses;


use common\components\soapResponse\interfaces\ISoapResponse;

class BaseResponse implements ISoapResponse
{
    protected function prepareResponseData($response): array
    {
        return [];
    }

    public function getMethodName(): string
    {
        return "UNKNOWN_METHOD";
    }
}