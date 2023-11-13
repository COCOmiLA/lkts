<?php

namespace common\services\messagesDeliver;

interface IMessageRecipient
{
    public function getRecipientAddress(): string;
}