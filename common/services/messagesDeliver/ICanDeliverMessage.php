<?php

namespace common\services\messagesDeliver;

use common\models\User;

interface ICanDeliverMessage
{
    



    public function setRecipient(User $recipient): ICanDeliverMessage;

    




    public function deliverMessage(string $title, string $message): array;
}