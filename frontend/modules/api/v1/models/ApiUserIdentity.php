<?php

namespace frontend\modules\api\v1\models;

use common\models\User;
use yii\filters\RateLimitInterface;




class ApiUserIdentity extends User implements RateLimitInterface
{

    


    public $rateWindowSize = 3600;

    






    public function getRateLimit($request, $action)
    {
        return [5000, $this->rateWindowSize];
    }

    






    public function loadAllowance($request, $action)
    {
        \Yii::$app->cache->get($this->getCacheKey('api_rate_allowance'));
        \Yii::$app->cache->get($this->getCacheKey('api_rate_timestamp'));
    }

    






    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        \Yii::$app->cache->set($this->getCacheKey('api_rate_allowance'), $allowance, $this->rateWindowSize);
        \Yii::$app->cache->set($this->getCacheKey('api_rate_timestamp'), $timestamp, $this->rateWindowSize);
    }

    



    public function getCacheKey($key)
    {
        return [__CLASS__, $this->getId(), $key];
    }
}
