<?php

namespace common\modules\abiturient\controllers;

use common\components\notification\factories\NotifierFactory;
use common\components\notification\NotificationService;
use common\components\notification\repositories\PopupNotificationRepository;
use common\models\notification\NotificationForm;
use common\models\notification\ReceiverSearch;
use Yii;
use yii\base\Action;
use yii\base\UserException;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\UploadedFile;

class NotificationController extends Controller
{
    
    protected $notification_service;

    public function getViewPath()
    {
        return \Yii::getAlias('@common/modules/abiturient/views/notification');
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
    
    public function init()
    {
        $this->notification_service = new NotificationService(new NotifierFactory());
        parent::init();
    }
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'controllers' => ['notification'],
                        'allow' => false,
                        'roles' => ['administrator'],
                    ],
                    [
                        'actions' => ['index', 'send'],
                        'allow' => true,
                        'roles' => ['manager']
                    ]
                ]
            ]
        ];
    }
    
    public function actions()
    {
        return ['error' => ['class' => \yii\web\ErrorAction::class]];
    }
    
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;
        $searchModel = new ReceiverSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        $listOfAdmissionCampaign = PopupNotificationRepository::getListOfAdmissionCampaignNonArchive($user->id);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'listOfAdmissionCampaign' => $listOfAdmissionCampaign
        ]);
    }
    
    public function actionSend()
    {
        $model = new NotificationForm();
        if ($model->load(Yii::$app->request->post())) {
            $model->types = Yii::$app->request->post('notification_type', []);
            $model->receivers = Yii::$app->request->post('receiver_selection', []);
            
            if ($model->validate()) {
                $model->attachments = UploadedFile::getInstances($model, 'attachments');
                $this->notification_service->send(
                    $model->types,
                    $model->title,
                    $model->body,
                    $model->receivers,
                    $model->attachments
                );
            } else {
                Yii::$app->session->setFlash("notificationError", $model->getErrors());
            }
        }
        
        return $this->redirect('index');
    }
}
