<?php

namespace common\modules\student\components\portfolio;

use common\models\User;
use Yii;
use yii\base\Component;

class PortfolioLoader extends Component implements \common\modules\student\interfaces\DynamicComponentInterface, \common\modules\student\interfaces\RoutableComponentInterface
{
    public $serviceUrl;

    public $guid;

    public $login;
    public $password;

    protected $client;

    public $componentName = 'Моё портфолио';
    public $baseRoute = 'student/portfolio';

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
            case (User::ROLE_TEACHER):
                return true;
            default:
                return false;
        }
    }


    public static function getConfig()
    {
        return [
            'class' => 'common\modules\student\components\portfolio\PortfolioLoader',
            'serviceUrl' => getenv('SERVICE_URI'),
            'login' => getenv("STUDENT_LOGIN"),
            'password' => getenv("STUDENT_PASSWORD"),
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\PortfolioController';
    }

    public static function getUrlRules()
    {
        return [
            'student/portfolio' => 'portfolio/index',
            'student/portfolio/form' => 'portfolio/form',
            'student/portfolio/comment' => 'portfolio/comment',
            'student/portfolio/upload' => 'portfolio/upload',
            'student/portfolio/evaluation' => 'portfolio/evaluation',
            'student/portfolio/file' => 'portfolio/file',
            'student/portfolio/mark' => 'portfolio/mark',
            'student/portfolio/mark-list' => 'portfolio/mark-list',
            'student/portfolio/dictionary' => 'portfolio/dictionary',
            'student/portfolio/delete-file' => 'portfolio/delete-file',
            'student/portfolio/delete-portfolio' => 'portfolio/delete-portfolio',
            'student/portfolio/students' => 'portfolio/students',
            'student/portfolio/ap' => 'portfolio/ap',

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

    public function loadPlanTree($ownerId, $ownerType, $ownerProperties)
    {
        return Yii::$app->getPortfolioService->loadPlanTree($ownerId, $ownerType, $ownerProperties);
    }

    public function loadLapResults($planUID, $lapUID)
    {
        return Yii::$app->getPortfolioService->loadLapResults($planUID, $lapUID);
    }

    public function loadLapResultClasses($planUID, $lapUID)
    {
        return Yii::$app->getPortfolioService->loadLapResultClasses($planUID, $lapUID);
    }

    public function loadLapResultClassesProperties($PlanUID, $LapUID, $LapResultClassUID)
    {
        return Yii::$app->getPortfolioService->loadLapResultClassesProperties($PlanUID, $LapUID, $LapResultClassUID);
    }

    public function loadRecordbooks()
    {
        return Yii::$app->getPortfolioService->loadRecordbooks(Yii::$app->user->identity->userRef->reference_id);
    }

    public function loadAttachedFileList($RefUID, $RefClassName)
    {
        return Yii::$app->getPortfolioService->loadAttachedFileList($RefUID, $RefClassName);
    }
}
