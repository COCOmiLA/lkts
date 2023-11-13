<?php

use yii\web\View;









?>

<div class="d-flex flex-column">
    <div class="d-flex flex-row mb-2">
        <div class="chat-about pl-2" data-destination_user_id="<?= $destinationUserId ?>">
            <div class="chat-with">
                <?php $chatWithPk = Yii::t(
                    'abiturient/chat/intrant-chat-destination-head-temp',
                    'Никнейм для блока контактов в чате абита: `Чат с модератором`'
                );
                if (!empty($chatPeople->nickname)) {
                    $chatWithPk .= " ({$chatPeople->nickname})";
                }
                echo $chatWithPk; ?>
            </div>

            <div class="chat-num-messages">
                <?= Yii::t(
                    'abiturient/chat/intrant-chat-destination-head-temp',
                    'Текст для счётчика сообщений в чате, в блоке истории чата: `Общее количество сообщений в чате: <span>{totalMessagesCount}</span>`',
                    ['totalMessagesCount' => $totalMessagesCount]
                ) ?>
            </div>
        </div>
    </div>
</div>