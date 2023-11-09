<?php

use common\modules\abiturient\models\chat\AbiturientChatUser;
use common\modules\abiturient\models\chat\ChatUserBase;
use yii\web\View;








?>

<p class="chat_horizontal_line_with_text mb-2">
    <span>
        <?= $chatCategoryTitle ?>
    </span>
</p>

<?php foreach ($peopleList as $chatPeople) : ?>
    <?php 
    $notReadMessagesCount = $chatPeople->getNotReadMessagesCount(); ?>

    <li class="mb-2">
        <button class="btn btn-secondary btn-block chat-select-btn p-1" data-destination_id="<?= $chatPeople->user_id ?>" data-chat_id="<?= $chatPeople->chat_id ?>">
            <div class="d-flex flex-row">
                <?php if ($notReadMessagesCount > 0) : ?>
                    <div class="align-self-start badge badge-warning mr-n3">
                        <?= $notReadMessagesCount ?>
                    </div>
                <?php endif; ?>

                <?php $altAvatar = Yii::t('manager/chat/history', 'Текст для для атрибута аватарки, в блоке истории чата: `Фотография поступающего`');
                if (
                    $chatPeople instanceof AbiturientChatUser &&
                    $avatarLink = AbiturientChatUser::getAvatarLink($chatPeople->user_id)
                ) : ?>
                    <img class="instead-avatar mr-1" src="<?= $avatarLink ?>" alt="<?= $altAvatar ?>">
                <?php endif; ?>

                <div class="d-flex justify-content-center align-items-center flex-fill">
                    <?= $chatPeople->shortNickNameForContactList; ?>
                </div>
            </div>
        </button>
    </li>
<?php endforeach;
