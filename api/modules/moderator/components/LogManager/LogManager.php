<?php

namespace api\modules\moderator\components\LogManager;

use common\components\ArrayToXmlConverter\ArrayToXmlConverter;
use common\models\DebuggingSoap;

class LogManager
{
    public static function IsLoggingEnabled(): bool
    {
        $soapDebuggingSetting = DebuggingSoap::getInstance();
        return $soapDebuggingSetting->enable_api_debug;
    }

    public static function LogXML($xml, $category, $prefix = '')
    {
        $needToLog = LogManager::IsLoggingEnabled();
        if ($needToLog) {
            if (!is_string($xml)) {
                $xml = ArrayToXmlConverter::to_xml($xml);
            }
            \Yii::warning($prefix . $xml, $category);
        }
    }
}