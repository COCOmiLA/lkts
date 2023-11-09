<?php

namespace common\modules\student\components\umk;

use common\models\User;
use Yii;
use yii\base\Component;

class UmkLoader extends Component implements \common\modules\student\interfaces\DynamicComponentInterface, \common\modules\student\interfaces\RoutableComponentInterface
{
    public $serviceUrl;

    public $login;
    public $password;

    protected $client;

    public $componentName = 'Учебно-методические материалы';
    public $baseRoute = 'student/umk';

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
            case (User::ROLE_TEACHER):
            case (User::ROLE_STUDENT):
                return true;
            default:
                return false;
        }
    }

    public static function getConfig()
    {
        return [
            'class' => 'common\modules\student\components\umk\UmkLoader',
            'login' =>  getenv('STUDENT_LOGIN'),
            'password' => getenv('STUDENT_PASSWORD'),
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\UmkController';
    }

    public static function getUrlRules()
    {
        return [
            'student/umk' => 'umk/index',
            'student/umk/discipline' => 'umk/discipline',
            'student/umk/discipline-caf' => 'umk/discipline-caf',
        ];
    }

    protected function buildUrl($type)
    {
        $urlTemplate = '';
        $url = null;

        if (substr($url, -1) != '/') {
            $urlTemplate = $url . '/';
        } else {
            $urlTemplate = $url;
        }

        $url = $urlTemplate;

        return $url;
    }

    public function loadRecordbooks()
    {
        return Yii::$app->getPortfolioService->loadRecordbooks(Yii::$app->user->identity->userRef->reference_id);
    }

    public function loadPlanTree($ownerId, $ownerType, $ownerProperties)
    {
        return Yii::$app->getPortfolioService->loadPlanTree($ownerId, $ownerType, $ownerProperties);
    }
}
