<?php


namespace common\components\secureUrlManager;


use Yii;
use yii\helpers\Url;
use yii\web\UrlManager;

class SecureUrlManager extends UrlManager
{
    



    private $_suspend_adding_referrer = false;
    
    public function getHostInfo()
    {
        $hostInfo = parent::getHostInfo();
        if (SecureUrlManager::isHttpsEnabled()) {
            $hostInfo = preg_replace('/http:\/\//', 'https://', $hostInfo);
        }
        return $hostInfo;
    }

    public static function isHttpsEnabled(): bool
    {
        return in_array(getenv('ENABLE_HTTPS'), ['true', '1', 1, 'on', 'yes']);
    }
    
    public function createUrl($params)
    {
        if (is_string($params)) {
            $params = [$params];
        }

        if ($this->_suspend_adding_referrer === true) {
            return parent::createUrl($params);
        }
        
        
        if (isset($params[0]) && str_contains($params[0], Yii::$app->controller->getRoute())) {
            return parent::createUrl($params);
        }
        
        return parent::createUrl(
            array_merge(
                $params,
                ['_referrer' => Url::current(['_referrer' => null])]
            )
        );
    }
    
    public function suspendAddingReferrerParam(bool $state): void
    {
        $this->_suspend_adding_referrer = $state;
    }
}
