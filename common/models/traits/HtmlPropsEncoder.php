<?php

namespace common\models\traits;

use Yii;

trait HtmlPropsEncoder
{
    protected function encodeProp($name, $value)
    {
        $excluded = $this->getExcludedFromEncodingProps();
        if (is_string($value) && !in_array($name, $excluded)) {
            $value = trim((string)$value);
            $value = htmlspecialchars((string)$value, ENT_NOQUOTES | ENT_SUBSTITUTE, Yii::$app ? Yii::$app->charset : 'UTF-8');
            $value = $this->utf8_for_xml($value);
        }
        return $value;
    }

    protected function utf8_for_xml($string)
    {
        return preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $string);
    }

    


    public function __set($name, $value)
    {
        $value = $this->encodeProp($name, $value);
        parent::__set($name, $value);
    }

    protected function getExcludedFromEncodingProps(): array
    {
        return [];
    }
}