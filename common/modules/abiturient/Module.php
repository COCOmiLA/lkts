<?php

namespace common\modules\abiturient;

use common\modules\abiturient\controllers\AbiturientController;
use common\modules\abiturient\controllers\BachelorController;
use common\modules\abiturient\controllers\ChatController;
use common\modules\abiturient\controllers\ClientErrorReceiverController;
use common\modules\abiturient\controllers\ContractorController;
use common\modules\abiturient\controllers\ManagerChatController;
use common\modules\abiturient\controllers\NotificationController;
use common\modules\abiturient\controllers\NotificationWidgetController;
use common\modules\abiturient\controllers\ResubmissionController;
use common\modules\abiturient\controllers\SandboxController;
use common\modules\abiturient\controllers\TransferController;
use Yii;

class Module extends \yii\base\Module implements \yii\base\BootstrapInterface
{
        public $controllerNamespace = 'common\modules\abiturient\controllers';

        public function bootstrap($app)
        {
                Yii::$app->controllerMap['abiturient'] = AbiturientController::class;
                Yii::$app->controllerMap['bachelor'] = BachelorController::class;
                Yii::$app->controllerMap['sandbox'] = SandboxController::class;
                Yii::$app->controllerMap['notification'] = NotificationController::class;
                Yii::$app->controllerMap['resubmission'] = ResubmissionController::class;
                Yii::$app->controllerMap['transfer'] = TransferController::class;
                Yii::$app->controllerMap['chat'] = ChatController::class;
                Yii::$app->controllerMap['manager-chat'] = ManagerChatController::class;
                Yii::$app->controllerMap['notification-widget'] = NotificationWidgetController::class;
                Yii::$app->controllerMap['client-error-receiver'] = ClientErrorReceiverController::class;
                Yii::$app->controllerMap['contractor'] = ContractorController::class;
        }
}
