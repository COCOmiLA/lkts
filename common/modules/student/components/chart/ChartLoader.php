<?php

namespace common\modules\student\components\chart;

use common\models\User;

class ChartLoader extends \yii\base\Component implements \common\modules\student\interfaces\DynamicComponentInterface, \common\modules\student\interfaces\RoutableComponentInterface
{
    public $componentName = 'Результаты освоения программы';
    public $baseRoute = 'student/chart';

    public static function getConfig()
    {
        return [
            'class' => 'common\modules\student\components\chart\ChartLoader',
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\ChartController';
    }

    public static function getUrlRules()
    {
        return [
            'student/chart' => 'chart/index',
        ];
    }

    public function getComponentName()
    {
        return $this->componentName;
    }

    public function getBaseRoute()
    {
        return $this->baseRoute;
    }

    public function isAllowedToRole($role)
    {
        switch ($role) {
            case (User::ROLE_STUDENT):
                return true;
            default:
                return false;
        }
    }
}
