<?php

use common\models\User;
use common\modules\abiturient\models\chat\EndedChatUser;
use common\modules\abiturient\models\chat\ManagerChatUser;
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
                            'Подпись для категории "новые чаты", в блоке списка контактов: `Новые чаты`'
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
                            'Подпись для категории "Старые чаты", в блоке списка контактов: `Старые чаты`'
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
                            'Подпись для категории "Завершённые чаты", в блоке списка контактов: `Завершённые чаты`'
                        ),
                        'peopleList' => $availableUsersWithEndingChats,
                    ]
                ) ?>
            <?php endif; ?>
        </ul>
    </div>
</div>