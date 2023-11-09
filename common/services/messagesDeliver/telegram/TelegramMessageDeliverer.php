<?php

namespace common\services\messagesDeliver\telegram;

use common\models\interfaces\IConfigurable;
use common\models\User;
use common\services\messagesDeliver\ICanDeliverMessage;
use yii\base\BaseObject;






class TelegramMessageDeliverer extends BaseObject implements \common\services\messagesDeliver\ICanDeliverMessage, IConfigurable
{
    public const BOT_TOKEN_ENV = 'TELEGRAM_BOT_TOKEN';

    public string $bot_token;
    private TelegramRecipient $recipient;

    



    public function setRecipient(User $recipient): TelegramMessageDeliverer
    {
        $this->recipient = new TelegramRecipient($recipient);
        return $this;
    }

    


    public function deliverMessage(string $title, string $message): array
    {
        if (!$this->isConfigured()) {
            return [false, 'Параметры Telegram Бота не настроены'];
        }
        $chat_id = $this->recipient->getRecipientAddress();
        if (empty($chat_id)) {
            return [false, 'Не указан chat_id'];
        }
        try {
            $apiToken = $this->bot_token;
            $data = [
                'chat_id' => $chat_id,
                'text' => "$title\n$message",
            ];
            $response = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($data));

            $response = json_decode($response, true);
            if (isset($response['ok']) && $response['ok']) {
                return [true, null];
            } else {
                return [false, $response['description'] ?? null];
            }
        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->bot_token);
    }
}