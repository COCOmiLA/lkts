<?php

use yii\bootstrap4\ButtonDropdown;
use yii\bootstrap4\Html;
use yii\helpers\Url;
use yii\web\View;












?>

<div class="d-flex flex-column">
    <div class="d-flex flex-row mb-2">
        <?php $altAvatar = Yii::t('manager/chat/history', 'Текст для для атрибута аватарки, в блоке истории чата: `Фотография поступающего`');
        if ($avatarLink) : ?>
            <img class="instead-avatar" src="<?= $avatarLink ?>" alt="<?= $altAvatar ?>">
        <?php endif; ?>

        <div class="chat-about pl-2" data-destination_user_id="<?= $destinationUserId ?>">
            <div class="chat-with">
                <?= $nickname ?>
            </div>

            <div class="chat-num-messages">
                <?= Yii::t(
                    'manager/chat/history',
                    'Текст для счётчика сообщений в чате, в блоке истории чата: `Общее количество сообщений в чате: <span>{totalMessagesCount}</span>`',
                    ['totalMessagesCount' => $totalMessagesCount]
                ) ?>
            </div>
        </div>
    </div>

    <?php if ($additionalUserInfoList) : ?>
        <?= $this->render(
            'additional_user_info',
            ['additionalUserInfoList' => $additionalUserInfoList]
        ) ?>
    <?php endif; ?>

    <div class="d-flex flex-row justify-content-between">
        <?= Html::button(
            Yii::t('manager/chat/header', 'Подпись кнопки для завершения чата с поступающим: `Завершить обращение`'),
            [
                'id' => 'chat-end-btn',
                'class' => 'btn btn-danger',
                'data-url' => Url::to(['/manager-chat/end-chat', 'destination_id' => $destinationUserId]),
            ]
        ) ?>

        <?= ButtonDropdown::widget([
            'label' => Yii::t('manager/chat/header', 'Подпись кнопки для переадресации чата на другого модератора: `Перенаправить заявку`'),
            'options' => ['class' => 'btn-group'],
            'buttonOptions' => [
                'class' => 'btn btn-success',
                'data-destination_user_id' => $destinationUserId,
            ],
            'dropdown' => [
                'items' => $otherManagers,
                'options' => ['id' => "chat-redirect-btn-{$destinationUserId}"]
            ],
        ]); ?>
    </div>
</div>