<?php

namespace backend\controllers;

use common\components\ini\iniSet;
use common\components\TextSettingsManager\TextSettingsManager;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;

class UpdateController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [[
                    'allow' => true,
                    'roles' => [User::ROLE_ADMINISTRATOR]
                ]],
            ],
            'time' => [
                'class' => 'common\components\EnvironmentManager\filters\TimeSyncCheckFilter',
            ],
        ];
    }

    public function actionIndex()
    {
        $session = \Yii::$app->session;
        try {
            $result = Yii::$app->soapClientAbit->load_with_caching("GetReleaseVersion");
            if (!empty($result->return)) {
                $version1C = $result->return;
            } else {
                $version1C = "Невозможно получить версию сервисов 1С (информация отсутствует)";
            }
        } catch (\Throwable $e) {
            $version1C = 'Ошибка обращения к методу GetReleaseVersion (' . $e->getMessage() . ').';
        }

        $result = (new Migrate())->getNewMigrate() ||
            (new Migrate(Migrate::TYPE_RBAC))->getNewMigrate();

        if ($session->hasFlash('migrate')) {
            $message = $session->getFlash('migrate');
        } else {
            $message = '';
        }

        if (TextSettingsManager::isDefaultSettingsChanged()) {
            $session->setFlash('text-settings-changed', [
                'body' => Yii::t(
                    'backend',
                    'В портале используются текстовые сообщения отличные от стандартных, рекомендуется проверить ' . Html::a('значения', Url::to(['settings/text']))
                ),
                'options' => ['class' => 'alert-warning'],
            ]);
        }

        return $this->render(
            'index',
            [
                'versionPortal' => Yii::$app->version,
                'version1C' => $version1C,
                'versionPHP' => phpversion(),
                'result' => $result,
                'message' => $message,
            ]
        );
    }

    public function actionUpdate()
    {
        
        iniSet::disableTimeLimit();
        

        if ((new Migrate())->getNewMigrate()) {
            $message = (new Migrate())->applyNewMigrate();
        }
        if ((new Migrate(Migrate::TYPE_RBAC))->getNewMigrate()) {
            $message = (new Migrate(Migrate::TYPE_RBAC))->applyNewMigrate();
        }

        $session = Yii::$app->session;
        $session->setFlash('migrate', $message);

        return $this->redirect('index');
    }
}
