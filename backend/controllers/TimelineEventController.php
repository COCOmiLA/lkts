<?php

namespace backend\controllers;

use backend\models\search\TimelineEventSearch;
use backend\models\SystemLog;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\models\settings\CodeSetting;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;




class TimelineEventController extends Controller
{
    public $layout = 'common';

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_ADMINISTRATOR]
                    ],
                ],
            ],
            'time' => [
                'class' => 'common\components\EnvironmentManager\filters\TimeSyncCheckFilter',
                'only' => ['index'],
            ],
        ];
    }

    



    public function actionIndex()
    {
        $searchModel = new TimelineEventSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = [
            'defaultOrder' => ['created_at' => SORT_DESC]
        ];

        $result = (new Migrate())->getNewMigrate() ||
            (new Migrate(Migrate::TYPE_RBAC))->getNewMigrate();

        if (empty(getenv('MAIL_HOST')) || empty(getenv('MAIL_USERNAME'))) {
            $mailError = true;
        } else {
            $mailError = false;
        }

        $timeZoneError = false;
        $timeZoneLocal = date_default_timezone_get();
        $timeZoneGlobal = ini_get('date.timezone');
        if (strcmp($timeZoneLocal, $timeZoneGlobal) || strlen((string)$timeZoneGlobal) < 1) {
            $timeZoneError = true;
        }

        $needToSetCode = CodeSettingsManager::NeedToFillCodes();

        return $this->render(
            'index',
            [
                'result' => $result,
                'needToSetCode' => $needToSetCode,
                'mailError' => $mailError,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'timeZoneError' => $timeZoneError,
                'hasMissingEnvironmentSettings' => EnvSettingsController::hasMissingEnvironmentSettings(),
            ]
        );
    }

    public function actionGetLatestLogs()
    {
        return $this->asJson(SystemLog::find()->select(['id', 'level', 'category'])->orderBy(['log_time' => SORT_DESC])->limit(5)->all());
    }
}
