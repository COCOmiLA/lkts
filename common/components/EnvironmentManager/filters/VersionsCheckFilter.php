<?php

namespace common\components\EnvironmentManager\filters;

use Yii;
use yii\base\Action;
use yii\base\UserException;

class VersionsCheckFilter extends \yii\base\ActionFilter
{
    



    public function beforeAction($action)
    {
        if ($action->controller->module->id !== 'backend') {
            if (!Yii::$app->releaseVersionProvider->isOneSServicesVersionMatches()) {
                throw new UserException(Yii::t(
                    'header/admin-interface',
                    'Предупреждение о том, что версия Информационной системы вуза не удовлетворяет минимальным требованиям к версии сервисов: `версия Информационной системы вуза не удовлетворяет минимальным требованиям Портала к версии сервисов.`',
                ));
            }
        }
        return parent::beforeAction($action);
    }
}
