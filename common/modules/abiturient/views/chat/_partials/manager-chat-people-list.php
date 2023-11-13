<?php

use common\models\User;
use common\modules\abiturient\models\chat\AbiturientChatUser;
use yii\web\View;









?>

<div class="row">
    <div class="col-12 people-list">
        <ul class="list py-0 px-1 m-0">
            <?php if ($availableUsersWithoutChats) : ?>
                <?= $this->render(
                    'group-people-list',
                    [
                        'chatCategoryTitle' => Yii::t(
                            '_partial/chat/list',
                            'Подпись для категории "Необработанные", в блоке списка контактов: `Необработанные`'
                        ),
                        'peopleList' => $availableUsersWithoutChats,
                    ]
                ) ?>
            <?php endif; ?>

            <?php if ($availableUsersWithChats) : ?>
                <?= $this->render(
                    'group-people-list',
                    [
                        'chatCategoryTitle' => Yii::t(
                            '_partial/chat/list',
                            'Подпись для категории "Назначенные на меня", в блоке списка контактов: `Назначенные<br>на <i>меня</i>`'
                        ),
                        'peopleList' => $availableUsersWithChats,
                    ]
                ) ?>
            <?php endif; ?>

            <?php if ($availableUsersWithEndingChats) : ?>
                <?= $this->render(
                    'group-people-list',
                    [
                        'chatCategoryTitle' => Yii::t(
                            '_partial/chat/list',
                            'Подпись для категории "Решённые", в блоке списка контактов: `Решённые`'
                        ),
                        'peopleList' => $availableUsersWithEndingChats,
                    ]
                ) ?>
            <?php endif; ?>
        </ul>
    </div>
</div>