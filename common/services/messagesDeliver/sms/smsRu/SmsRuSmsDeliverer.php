<?php

namespace common\services\messagesDeliver\sms\smsRu;

use common\services\messagesDeliver\sms\SmsDeliverer;
use stdClass;




class SmsRuSmsDeliverer extends SmsDeliverer
{
    public const SMSRU_API_KEY_ENV = 'SMSRU_API_KEY';

    public string $api_key;

    public function deliverMessage(string $title, string $message): array
    {
        if (!$this->isConfigured()) {
            return [false, 'Параметры SMS-шлюза не настроен'];
        }
        try {
            $smsru = new SMSRU($this->api_key); 

            $data = new stdClass();
            $data->to = $this->recipient->getRecipientAddress();
            $data->text = "$title\n$message"; 
            $sms = $smsru->send_one($data); 

            if ($sms->status == "OK") { 
                return [true, null];
            } else {
                return [false, $sms->status_code . ': ' . $sms->status_text];
            }
        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->api_key);
    }
}