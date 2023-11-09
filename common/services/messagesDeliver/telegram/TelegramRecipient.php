<?php

namespace common\services\messagesDeliver\telegram;

use backend\models\ManagerNotificationSetting;
use common\models\User;
use yii\helpers\ArrayHelper;

class TelegramRecipient implements \common\services\messagesDeliver\IMessageRecipient
{
    private string $chat_id;

    public function __construct(User $user)
    {
        $telegram_setting = ManagerNotificationSetting::findOne(['manager_id' => $user->id, 'name' => 'telegram_chat_id']);
        $this->chat_id = ArrayHelper::getValue($telegram_setting, 'value') ?? '';
    }

    public function getRecipientAddress(): string
    {
        return $this->chat_id;
    }
}