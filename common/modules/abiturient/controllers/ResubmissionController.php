<?php

namespace common\modules\abiturient\controllers;

use common\components\BooleanCaster;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\ResubmissionUserSettingsSearch;
use Yii;
use yii\base\Action;
use yii\base\UserException;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class ResubmissionController extends Controller
{

    public function getViewPath()
    {
        return \Yii::getAlias('@common/modules/abiturient/views/resubmission');
    }

    





    public function beforeAction($action)
    {
        $allowMasterSystemManager = Yii::$app->configurationManager->getMasterSystemManagerSetting('use_master_system_manager_interface');
        if ($allowMasterSystemManager && $action->id !== 'informing') {
            $this->redirect(['sandbox/informing', 'name' => 'system_manager.manager_is_not_allowed']);
            return true;
        }

        return parent::beforeAction($action);
    }


    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [User::ROLE_MANAGER]
                    ]
                ]
            ]
        ];
    }

    public function actionManage()
    {
        $user = Yii::$app->user->identity;
        $searchModel = new ResubmissionUserSettingsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $listOfAdmissionCampaign = $user->getCampaignsToModerate()
            ->joinWith(['campaign.referenceType referenceType'])
            ->select([ApplicationType::tableName() . '.name name', 'referenceType.reference_uid reference_uid'])
            ->distinct()
            ->asArray()
            ->all();

        return $this->render('manage', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'listOfAdmissionCampaign' => $listOfAdmissionCampaign
        ]);
    }

    public function actionChangePermissions()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $user_id_type_ids = Yii::$app->request->post('user_id_type_ids');
        $permission = BooleanCaster::cast(Yii::$app->request->post('permission'));
        if ($user_id_type_ids) {
            foreach ($user_id_type_ids as ['user_id' => $user_id, 'type_id' => $type_id]) {
                $user = User::findOne($user_id);
                $type = ApplicationType::findOne($type_id);
                if ($user && $type) {
                    $type->toggleResubmitPermissions($user, $permission);
                }
            }
            return ['success' => true];
        }
        return ['success' => false, 'message' => 'Не выбран ни один поступающий'];
    }
}
