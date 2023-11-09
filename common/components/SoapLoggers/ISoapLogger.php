<?php

namespace common\components\SoapLoggers;


interface ISoapLogger
{
    public function doRequestLog($action, $startTime, $endTime, $request_data, $response_data): void;

    public function doErrorLog($action, $startTime, $request_data, $error): void;
}