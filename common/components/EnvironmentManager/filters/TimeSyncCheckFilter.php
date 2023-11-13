<?php

namespace common\components\EnvironmentManager\filters;

use common\components\CodeSettingsManager\exceptions\CodeNotFilledException;
use DateTime;
use Yii;
use yii\base\Action;
use yii\base\UserException;

class TimeSyncCheckFilter extends \yii\base\ActionFilter
{
    




    public function beforeAction($action)
    {
        $result = parent::beforeAction($action);
        if ($result) {
            $this->checkTimeSync();
        }
        return $result;
    }

    protected function getDateTimeFrom1C(): DateTime
    {
        $response = Yii::$app->soapClientAbit->load('GetCurrentTime');
        if (isset($response->return)) {
            return new DateTime($response->return);
        }

        return new DateTime();
    }

    protected function checkTimeSync(): void
    {
        if (YII_ENV_DEV) {
            return;
        }
        
        $result = Yii::$app->cache->get('timeSyncCheck');
        if ($result === false) {
            $result = $this->checkTimeSyncFrom1C();
            if ($result == 'ok') {
                
                Yii::$app->cache->set('timeSyncCheck', $result, 3600);
            }
        }
        if ($result === 'error') {
            throw new UserException('Время на сервере не синхронизировано с Информационной системой вуза');
        }
    }

    protected function checkTimeSyncFrom1C(): string
    {
        $local_time = new DateTime();
        $one_s_time = $this->getDateTimeFrom1C();
        $diff = $local_time->diff($one_s_time);
        $diff_in_seconds = $this->getTotalSeconds($diff);
        if ($diff_in_seconds > 20 || $diff_in_seconds < -20) {
            return 'error';
        }
        return 'ok';
    }

    







    private function getTotalSeconds(\DateInterval $dateInterval)
    {
        $iSeconds = $dateInterval->s + ($dateInterval->i * 60) + ($dateInterval->h * 3600);

        if ($dateInterval->days > 0)
            $iSeconds += ($dateInterval->days * 86400);

        
        else
            $iSeconds += ($dateInterval->d * 86400) + ($dateInterval->m * 2592000) + ($dateInterval->y * 31536000);

        if ($dateInterval->invert)
            $iSeconds *= -1;

        return $iSeconds;
    }
}
