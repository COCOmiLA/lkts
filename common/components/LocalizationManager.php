<?php

namespace common\components;

use Yii;

class LocalizationManager extends \yii\base\Component
{
    private $_locales = [];

    public function init()
    {
        parent::init();
        $this->_locales = Yii::$app->params['availableLocales'];
    }

    public function getAvailableLocales(bool $with_description = false)
    {
        if ($with_description) {
            return $this->_locales;
        }
        return array_keys($this->_locales);
    }
}