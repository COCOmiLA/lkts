<?php

namespace common\services\messagesDeliver\sms\ssms;

use common\services\messagesDeliver\sms\SmsDeliverer;
use Yii;





class SsmsSmsDeliverer extends SmsDeliverer
{
    public const SMSS_API_KEY_ENV = 'SMSS_API_KEY';

    public string $api_key;

    public function deliverMessage(string $title, string $message): array
    {
        if (!$this->isConfigured()) {
            return [false, 'Параметры SMS-шлюза не настроен'];
        }
        try {
            $result = $this->smsapi_push_msg_nologin_key($this->api_key, $this->recipient->getRecipientAddress(), "$title\n$message");
        } catch (\Throwable $e) {
            Yii::error("Не удалось отправить СМС на номер {$this->recipient->getRecipientAddress()} по причине: {$e->getMessage()}");
            return [false, $e->getMessage()];
        }
        if (!$result || !isset($result[0]) || $result[0] != 0) {
            return [false, "Ошибка отправки SMS"];
        }
        return [true, null];
    }

    public function isConfigured(): bool
    {
        return !empty($this->api_key);
    }

    private function smsapi_push_msg_nologin_key($key, $phone, $text, $params = null)
    {
        $req = array(
            "method" => "push_msg",
            "api_v" => "2.0",
            "key" => $key,
            "phone" => $phone,
            "text" => $text);
        if (!is_null($params)) {
            $req = array_merge($req, $params);
        }
        $resp = $this->_smsapi_communicate($req);
        if (is_null($resp)) {
            
            return null;
        }
        $ec = $resp[0];
        if ($ec != 0) {
            return array($ec);
        }
        if (!isset($resp[1]['n_raw_sms']) or !isset($resp[1]['credits'])) {
            return null; 
        }
        $n_raw_sms = $resp[1]['n_raw_sms'];
        $credits = $resp[1]['credits'];
        $id = $resp[1]['id'];
        return array(0, $n_raw_sms, $credits, $id);
    }

    










    private function _smsapi_communicate($request, $cookie = NULL)
    {
        $request['format'] = "json";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://api2.ssms.su/");
        
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if (!is_null($cookie)) {
            curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        }
        $data = curl_exec($curl);
        curl_close($curl);
        if ($data === false) {
            return null;
        }
        $js = json_decode($data, $assoc = true);
        if (!isset($js['response'])) return null;
        $rs = &$js['response'];
        if (!isset($rs['msg'])) return null;
        $msg = &$rs['msg'];
        if (!isset($msg['err_code'])) return null;
        $ec = intval($msg['err_code']);
        if (!isset($rs['data'])) {
            $data = null;
        } else {
            $data = $rs['data'];
        }
        return array($ec, $data);
    }

}