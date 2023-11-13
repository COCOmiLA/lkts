<?php

namespace common\components;

class Request extends \yii\web\Request
{
    public function getReferrer()
    {
        $_referrer = $this->get('_referrer');
        if ($_referrer) {
            return $_referrer;
        }
        return parent::getReferrer();
    }
}