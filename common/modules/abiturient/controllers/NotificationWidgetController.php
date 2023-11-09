<?php

namespace common\modules\abiturient\controllers;

use common\components\filesystem\FilterFilename;
use common\components\notification\repositories\PopupNotificationRepository;
use common\models\notification\Notification;
use common\models\notification\NotificationAttachment;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class NotificationWidgetController extends Controller
{
    public function getViewPath()
    {
        return \Yii::getAlias('@common/modules/abiturient/views/abiturient');
    }
    
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['new', 'unread-count', 'max-id', 'read', 'download-file'],
                        'allow' => true,
                        'roles' => ['abiturient']
                    ]
                ],
            ],
        ];
    }
    
    public function actionNew()
    {
        $max_id = Yii::$app->request->post('max_id');
        $user_id = Yii::$app->user->identity->id;
        $models = PopupNotificationRepository::getNew($user_id, $max_id)->all();
        
        return $this->renderPartial('@common/components/notification/widgets/views/_list', [
            'models' => $models
        ]);
    }
    
    public function actionMaxId()
    {
        $count = PopupNotificationRepository::getReceiverNotifications(Yii::$app->user->identity->id)
            ->select('id')
            ->limit(1)
            ->scalar();
        
        return $this->asJson(intval($count));
    }
    
    public function actionUnreadCount()
    {
        $count = PopupNotificationRepository::getUnreadCount(Yii::$app->user->identity->id)->count();
        return $this->asJson($count);
    }
    
    public function actionRead()
    {
        $id = Yii::$app->request->post('id');
        $model = $this->loadModel($id);
        return $this->asJson($model->markAsRead());
    }
    
    public function actionDownloadFile($attachment_id)
    {
        $attachment = NotificationAttachment::findOne($attachment_id);
        if ($attachment === null) {
            throw new NotFoundHttpException('Не удалось найти файл.');
        }
        
        $user = Yii::$app->user->identity;
        if (!$attachment->checkAccess($user)) {
            throw new ForbiddenHttpException();
        }
        
        $abs_path = $attachment->getAbsPath();
        if ($abs_path && file_exists($abs_path)) {
            return Yii::$app->response->sendFile(
                $abs_path,
                $this->filterFilename($attachment->filename),
                [
                    'mimeType' => $attachment->getMimeType(),
                    'inline' => $attachment->extension === 'pdf'
                ]
            );
        }
        
        throw new NotFoundHttpException('Невозможно скачать файл.');
    }
    
    protected function loadModel($id): Notification
    {
        $model = Notification::findOne($id);
        
        if ($model === null) {
            throw new NotFoundHttpException("Не найдено уведомление");
        }
        
        $this->checkAccess($model);
        
        return $model;
    }
    
    




    protected function checkAccess(Notification $model): bool
    {
        if ($model->receiver_id != Yii::$app->user->identity->id) {
            throw new ForbiddenHttpException();
        }
        
        return true;
    }
    
    private function filterFilename(string $filename): string
    {
        return FilterFilename::sanitize($filename);
    }
}
