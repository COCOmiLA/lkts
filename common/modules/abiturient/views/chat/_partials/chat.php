<?php

use yii\web\View;





?>

<div class="chat-header"></div>

<div class="chat-history mt-2 mb-2 pr-2 flex-fill">
    <ul class="p-0 m-0">
        <li class="empty-chat-history d-flex justify-content-center align-items-center">
            <?= Yii::t(
                '_partial/chat/history',
                'Текст пустой истории чата, в блоке истории чата: `Выберите чат из списка слева`'
            ) ?>
        </li>
    </ul>
</div>

<div class="chat-message d-flex flex-column">
    <?php $textPlaceholder = Yii::t(
        '_partial/chat/message',
        'Подпись пустого значения для области ввода сообщения, в блоке истории сообщений: `Введите текст сообщения`'
    ) ?>
    <textarea class="form-control" name="message-to-send" id="message-to-send" placeholder="<?= $textPlaceholder ?>" rows="3" disabled></textarea>

    <div class="d-flex flex-row justify-content-between mt-2">
        <?= $this->render('chat_file_uploader') ?>

        <button class="ml-auto" id="send-btn-message">
            <?= Yii::t(
                '_partial/chat/message',
                'Подпись для кнопки отправки сообщения, в блоке истории сообщений: `Отправить`'
            ) ?>
        </button>
    </div>
</div>