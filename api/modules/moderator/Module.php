<?php

namespace api\modules\moderator;

use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Request;
use yii\web\Response;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'moderatorApi\modules\v1\controllers';

    public function init()
    {
        Yii::$app->response->format = Response::FORMAT_XML;
        $this->modules = [
            'v1' => [
                'class' => \api\modules\moderator\modules\v1\Module::class,
            ],
        ];

        parent::init();
    }

    




    public function beforeAction($action)
    {
        $isMasterSystemManagerEnabled = Yii::$app->configurationManager->getMasterSystemManagerSetting('use_master_system_manager_interface');

        if (!$isMasterSystemManagerEnabled) {
            throw new ForbiddenHttpException('Использование интерфейса модератора запрещено. Обратитесь к администратору системы для уточнения настроек.');
        }

        
        $request = Yii::$app->request;
        $header = $request->getHeaders()->get('Authorization');

        if (empty($header)) {
            throw new ForbiddenHttpException('Не передан заголовок авторизации.');
        }

        if (preg_match("/AdmissionCampaign ([a-zA-Z0-9\-_]+)/", $header, $matches) !== 1) {
            throw new ForbiddenHttpException('Аутентификация невозможна. Некорректный хэдер авторизации.');
        }

        $token = $matches[1] ?? null;

        if (is_null($token)) {
            $this->throwForbiddenException();
        }

        $campaign = AdmissionCampaign::findOne([
            'api_token' => $token,
            'archive' => false
        ]);

        if (is_null($campaign)) {
            $this->throwForbiddenException();
        }

        return parent::beforeAction($action);
    }

    


    private function throwForbiddenException()
    {
        throw new ForbiddenHttpException('Отказано в доступе. Убедитесь, что в панели администратора токены приемных кампаний были обновлены.');
    }
}
