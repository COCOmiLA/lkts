<?php

use common\models\settings\ChatSettings;
use common\models\User;
use common\modules\abiturient\assets\chatAsset\AbiturientChatAsset;
use common\modules\abiturient\models\chat\ManagerChatUser;
use yii\helpers\Url;
use yii\web\View;










AbiturientChatAsset::register($this);

$this->title = Yii::$app->name . ' | ' . Yii::t(
    'abiturient/chat/all',
    'Заголовок страницы Чата: `Личный кабинет поступающего | Чат с приёмной комиссией`'
);

$this->registerJsVar('createNewChatError', Yii::t('abiturient/chat/all', 'Ошибка при создании нового чата'));

$this->registerJsVar('openChatUrl', Url::to(['/chat/open-chat']));
$this->registerJsVar('sendMessageUrl', Url::to(['/chat/send-message']));
$this->registerJsVar('updateChatPeopleListUrl', Url::to(['/chat/entrant-update-chat-people-list']));
$this->registerJsVar('updateRequestInterval', intval(ChatSettings::getValueByName(ChatSettings::PARAM_REQUEST_INTERVAL)) * 1000);

$this->registerJsVar('currentUserId', $user->id);

$this->registerJs('var ChatClass = "Chat";', View::POS_END);

?>

<?= $this->render(
    '@chatPartialView/navigation-btn'
) ?>

<div class="d-flex flex-row chat-container">
    <div class="card navigation-card text-white bg-dark p-2 align-items-stretch">
        <div class="card-block d-flex flex-column">
            <?= $this->render(
                '@chatPartialView/chat-search'
            ) ?>

            <?= $this->render(
                '@chatPartialView/chat-people-list',
                compact([
                    'availableUsersWithChats',
                    'availableUsersWithoutChats',
                    'availableUsersWithEndingChats',
                ])
            ) ?>
        </div>
    </div>

    <div class="card bg-light p-2 flex-fill">
        <div class="card-block chat d-flex align-items-end flex-column">
            <?= $this->render('@chatPartialView/chat') ?>
        </div>
    </div>
</div>

<script id="incoming-template-message" type="text/x-handlebars-template">
    <?= $this->render(
        '@chatPartialView/incoming-message-template',
        [
            'time' => '{{time}}',
            'nickname' => '{{nickname}}',
            'messageUid' => '{{messageUid}}',
            'messageOutput' => '{{messageOutput}}',
        ]
    ) ?>
</script>

<script id="incoming-template-file" type="text/x-handlebars-template">
    <?= $this->render(
        '@chatPartialView/incoming-file-template',
        [
            'time' => '{{time}}',
            'nickname' => '{{nickname}}',
            'fileUid' => '{{fileUid}}',
            'fileName' => '{{fileName}}',
        ]
    ) ?>
</script>

<script id="outgoing-template-message" type="text/x-handlebars-template">
    <?= $this->render(
        '@chatPartialView/outgoing-message-template',
        [
            'time' => '{{time}}',
            'nickname' => '{{nickname}}',
            'messageUid' => '{{messageUid}}',
            'messageOutput' => '{{messageOutput}}',
        ]
    ) ?>
</script>

<script id="outgoing-template-file" type="text/x-handlebars-template">
    <?= $this->render(
        '@chatPartialView/outgoing-file-template',
        [
            'time' => '{{time}}',
            'nickname' => '{{nickname}}',
            'fileUid' => '{{fileUid}}',
            'fileName' => '{{fileName}}',
        ]
    ) ?>
</script>