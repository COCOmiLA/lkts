<?php

namespace common\components\notification\widgets;

use common\components\notification\repositories\PopupNotificationRepository;
use common\components\notification\widgets\assets\PopupNotificationWidgetAsset;
use common\models\notification\Notification;
use common\models\notification\NotificationSetting;
use Yii;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

class PopupNotificationWidget extends Widget
{
    
    public $user_id;

    
    public $page_size = 100;

    
    protected $unread_count;
    
    
    protected $data_provider;

    protected static $category_icon = [
        Notification::CATEGORY_COMMON => 'fa fa-exclamation-circle',
        Notification::CATEGORY_CHAT => 'fa fa-envelope'
    ];

    public function init()
    {
        if ($this->user_id === null) {
            $this->user_id = Yii::$app->user->identity->id;
        }
        
        $this->fetchData();
    }
    
    protected function fetchData()
    {
        $this->unread_count = PopupNotificationRepository::getUnreadCount($this->user_id)->count();
        $query = PopupNotificationRepository::getReceiverNotifications($this->user_id);
                
        $this->data_provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->page_size
            ]
        ]);
    }

    public function run()
    {
        $setting = NotificationSetting::findOne(['name' => NotificationSetting::PARAM_REQUEST_INTERVAL]);
        $this->getView()->registerJsVar('notificationRequestInterval', 
            ArrayHelper::getValue($setting, 'value', NotificationSetting::DEFAULT_REQUEST_INTERVAL));
        $this->getView()->registerJsVar('maxNotificationId', PopupNotificationRepository::getMaxId($this->user_id));
        PopupNotificationWidgetAsset::register($this->getView());
        
        return $this->render('notification_widget', [
            'unread_count' => $this->unread_count,
            'data_provider' => $this->data_provider
        ]);
    }
    
    public static function getIconClass(Notification $model): string
    {
        return static::$category_icon[$model->category] ?? 'fa fa-exclamation-circle';
    }
}
