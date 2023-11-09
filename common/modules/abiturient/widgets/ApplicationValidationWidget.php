<?php

namespace common\modules\abiturient\widgets;

use common\models\EmptyCheck;
use common\modules\abiturient\assets\validationAsset\ApplicationValidationAsset;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use Yii;
use yii\base\Widget;
use yii\bootstrap4\Alert;
use yii\helpers\Url;

class ApplicationValidationWidget extends Widget
{
    public BachelorApplication $app;

    public function run()
    {
        if (!$this->app) {
            return '';
        }
        $this->view->registerJsVar('url_to_validate', Url::to(['/bachelor/validate-application', 'id' => $this->app->id]));
        $this->view->registerJsVar('app_validate_error_message', Yii::t(
            'abiturient/header/all',
            'Текст ошибки о невозможности подачи заявления на панели навигации ЛК: `<strong>Внимание!</strong> Не все обязательные данные внесены.`'
        ));

        $alerts = Alert::widget([
            'options' => ['class' => 'alert-danger ajax-info', 'id' => 'app_validation_info'],
            'body' => '',
        ]);

        if ($this->app->draft_status == BachelorApplication::DRAFT_STATUS_CREATED) {
            $message = Yii::$app->configurationManager->getText('can_send_app_message');

            if (!EmptyCheck::isEmpty($message)) {
                $this->view->registerJsVar('can_send_app_message', $message);

                $this->view->registerJsVar('url_to_check_can_send', Url::to(['/bachelor/check-can-send-application', 'id' => $this->app->id]));
                $alerts .= Alert::widget([
                    'options' => ['class' => 'alert-warning ajax-info', 'id' => 'can_send_app_info'],
                    'body' => '',
                ]);
            }
        }

        ApplicationValidationAsset::register($this->view);

        return $alerts;
    }
}
