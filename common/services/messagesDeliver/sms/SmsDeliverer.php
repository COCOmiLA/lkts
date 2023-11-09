<?php

namespace common\services\messagesDeliver\sms;

use common\models\interfaces\IConfigurable;
use common\models\User;
use yii\base\BaseObject;

abstract class SmsDeliverer extends BaseObject implements \common\services\messagesDeliver\ICanDeliverMessage, IConfigurable
{
    protected SmsRecipient $recipient;

    



    public function setRecipient(User $recipient): SmsDeliverer
    {
        $this->recipient = new SmsRecipient($recipient);
        return $this;
    }

    


    public function deliverMessage(string $title, string $message): array
    {
        throw new \Exception('Not implemented');
    }

    public function isConfigured(): bool
    {
        return false;
    }
}