<?php

use common\components\DateTimeHelper;
use common\components\notification\widgets\PopupNotificationWidget;
use common\models\notification\Notification;
use yii\helpers\Html;
use yii\helpers\Url;

 
?>
<div class="list-group-item <?php echo $model->isUnread() ? 'list-group-item-new' : '' ?>" data-id="<?php echo $model->id ?>" >
    <div class="notification__box">
        <div class="notification__content">
            <div class="notification__icon">
                <i class="<?php echo PopupNotificationWidget::getIconClass($model) ?>"></i>
            </div>
            <div class="notification__text">
                <div class="notification__title ">
                    <?php echo Html::encode($model->popupTitle) ?>
                </div>
                <div class="notification__date">
                    <?php echo DateTimeHelper::dateFromMstime('d.m.Y, H:i', $model->created_at); ?>
                </div>

                <div class="notification__body collapse" id="notification-body-<?php echo $model->id ?>">
                    <?php echo Html::encode($model->body) ?>
                    <?php if ($model->notificationAttachments): ?>
                        <div class="notification__attachments">
                            <ul>
                                <?php foreach ($model->notificationAttachments as $attachment): ?>
                                    <li>
                                        <span class="fa fa-file"></span>
                                        <a class="btn btn-link" href="<?php echo Url::toRoute(['notification-widget/download-file', 'attachment_id' => $attachment->id]) ?>">
                                            <?php echo Html::encode($attachment->fileName) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="notification__expand">
                    <button class="btn btn-link notification__expand-btn" 
                        type="button" 
                        data-toggle="collapse" 
                        data-target="#notification-body-<?php echo $model->id ?>" 
                        data-id="<?php echo $model->id ?>"
                        aria-expanded="false" 
                        aria-controls="notification-body">
                        <?php echo Yii::t('notification/notification-widget', 'Подпись кнопки раскрытия уведомления в виджете уведомлений: `Подробнее`'); ?>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>