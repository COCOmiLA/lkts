<?php

namespace common\services\messagesDeliver\sms\smsAero;



class SmsAeroSmsDeliverer extends \common\services\messagesDeliver\sms\SmsDeliverer
{
    public const SMSAERO_EMAIL_ENV = 'SMSAERO_EMAIL';
    public const SMSAERO_API_KEY_ENV = 'SMSAERO_API_KEY';

    public string $email; 
    public string $api_key; 

    public function deliverMessage(string $title, string $message): array
    {
        if (!$this->isConfigured()){
            return [false, 'Параметры SMS-шлюза не настроен'];
        }
        try {
            $smsaero_api = new SmsaeroApiV2($this->email, $this->api_key); 
            $result = $smsaero_api->send($this->recipient->getRecipientAddress(), "$title\n$message"); 
            if ($result['success']) {
                return [true, null];
            }
            return [false, $result['message'] ?? null];
        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->email) && !empty($this->api_key);
    }
}