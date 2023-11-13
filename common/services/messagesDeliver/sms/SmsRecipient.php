<?php

namespace common\services\messagesDeliver\sms;

use common\models\User;
use yii\helpers\ArrayHelper;

class SmsRecipient implements \common\services\messagesDeliver\IMessageRecipient
{
    private string $phone;

    public function __construct(User $user)
    {
        $this->phone = ArrayHelper::getValue($user, 'abiturientQuestionary.personalData.main_phone');
    }

    public function getRecipientAddress(): string
    {
        $processed_phone = $this->phone;
        $processed_phone = str_replace('+', '', $processed_phone);
        $processed_phone = str_replace('(', '', $processed_phone);
        $processed_phone = str_replace(')', '', $processed_phone);
        $processed_phone = str_replace('-', '', $processed_phone);
        $processed_phone = str_replace('(', '', $processed_phone);
        $processed_phone = str_replace(')', '', $processed_phone);
        $processed_phone = str_replace('-', '', $processed_phone);
        $processed_phone = str_replace(' ', '', $processed_phone);
        return str_replace('_', '', $processed_phone);
    }
}