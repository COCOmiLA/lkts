<?php


namespace common\components\SoapLoggers;


use common\components\ArrayToXmlConverter\ArrayToXmlConverter;
use Yii;

class XmlWaySoapLogger implements ISoapLogger
{
    


    public function doRequestLog($action, $startTime, $endTime, $request_data, $response_data): void
    {
        if ($action == 'PutFilePart') {
            unset($request_data['PartData']);
        }
        Yii::warning("Начало обращения к методу $action в $startTime " . PHP_EOL . ArrayToXmlConverter::to_xml($request_data, $action . 'Request'), $action . '[Request]');
        if ($action == 'GetBinaryData') {
            Yii::warning("Полученный ответ в $endTime Бинарный", $action . '[Response]');
        } else {
            Yii::warning("Полученный ответ в $endTime " . PHP_EOL . ArrayToXmlConverter::to_xml($response_data, $action . 'Response'), $action . '[Response]');
        }
    }

    


    public function doErrorLog($action, $startTime, $request_data, $error): void
    {
        if ($action == 'PutFilePart') {
            unset($request_data['PartData']);
        }
        Yii::error("Начало обращения к методу $startTime", $action . '[Request][Time]');
        Yii::error('Ошибка обращения к методу ' . $action . ' (' . $error->getMessage() . ').' . PHP_EOL . ArrayToXmlConverter::to_xml($request_data, $action . 'Request'), $action . '[Request]');
    }
}