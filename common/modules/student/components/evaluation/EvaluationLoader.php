<?php

namespace common\modules\student\components\evaluation;

use common\models\User;
use Yii;
use yii\base\Component;

class EvaluationLoader extends Component implements \common\modules\student\interfaces\DynamicComponentInterface, \common\modules\student\interfaces\RoutableComponentInterface
{
    public $serviceUrl;

    public $guid;

    public $login;
    public $password;

    protected $client;

    public $componentName = 'Портфолио студентов';
    public $baseRoute = 'student/evaluation';

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
                return true;
            default:
                return false;
        }
    }


    public static function getConfig()
    {
        return [
            'class' => 'common\modules\student\components\evaluation\EvaluationLoader',
            'serviceUrl' => getenv('SERVICE_URI'),
            'login' => getenv('STUDENT_LOGIN'),
            'password' => getenv('STUDENT_PASSWORD'),
        ];
    }

    public static function getController()
    {
        return __NAMESPACE__ . '\\controllers\\EvaluationController';
    }

    public static function getUrlRules()
    {
        return [
            'student/evaluation' => 'evaluation/index',
            'student/evaluation/form' => 'evaluation/form',
            'student/evaluation/comment' => 'evaluation/comment',
            'student/evaluation/upload' => 'evaluation/upload',

            'student/evaluation/file' => 'evaluation/file',
            'student/evaluation/mark' => 'evaluation/mark',
            'student/evaluation/mark-list' => 'evaluation/mark-list',
            'student/evaluation/dictionary' => 'evaluation/dictionary',
            'student/evaluation/delete-file' => 'evaluation/delete-file',
            'student/evaluation/delete-portfolio' => 'evaluation/delete-portfolio',
            'student/evaluation/students' => 'evaluation/students',
            'student/evaluation/ap' => 'evaluation/ap',

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
