<?php

namespace common\services\messagesDeliver\email;

use common\services\messagesDeliver\IMessageRecipient;

class EmailRecipient implements IMessageRecipient
{
    private string $email;


    public function __construct(\common\models\User $user)
    {
        $this->email = $user->email;
    }

    public function getRecipientAddress(): string
    {
        return $this->email;
    }
}