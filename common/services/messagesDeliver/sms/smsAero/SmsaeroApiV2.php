<?php

namespace common\services\messagesDeliver\sms\smsAero;

class SmsaeroApiV2
{
    const URL_SMSAERO_API = 'https://gate.smsaero.ru/v2';
    private string $email; 
    private string $api_key; 
    private $sign = 'SMS Aero'; 

    public function __construct($email, $api_key, $sign = false)
    {
        $this->email = $email;
        $this->api_key = $api_key;
        if ($sign) {
            $this->sign = $sign;
        }
    }

    






    private function curl_post($url, array $post = NULL, array $options = array())
    {
        $defaults = array(
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => http_build_query($post),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERPWD => $this->email . ":" . $this->api_key,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        );

        $ch = curl_init();
        curl_setopt_array($ch, ($options + $defaults));
        if (!$result = curl_exec($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

    







    public function send($number, $text, $dateSend = null, $callbackUrl = null)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/sms/send/', [
            is_array($number) ? 'numbers' : 'number' => $number,
            'sign' => $this->sign,
            'text' => $text,
            'dateSend' => $dateSend,
            'callbackUrl' => $callbackUrl
        ]), true);
    }

    




    public function check_send($id)
    {
        return json_decode($this->curl_post(self::URL_SMSAERO_API . '/sms/status/', [
            'id' => $id
        ]), true);
    }

    



    public function balance()
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/balance', []), true);
    }

    



    public function tariffs()
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/tariffs', []), true);
    }

    




    public function sign_add($name)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/sign/add', [
            'name' => $name
        ]), true);
    }

    




    public function group_add($name)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/group/add', [
            'name' => $name
        ]), true);
    }

    




    public function group_delete($id)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/group/delete', [
            'id' => $id
        ]), true);
    }

    













    public function contact_add($number, $groupId = null, $birthday = null, $sex = null, $lname = null, $fname = null, $sname = null, $param1 = null, $param2 = null, $param3 = null)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/contact/add', [
            'number' => $number,
            'groupId' => $groupId,
            'birthday' => $birthday,
            'sex' => $sex,
            'lname' => $lname,
            'fname' => $fname,
            'sname' => $sname,
            'param1' => $param1,
            'param2' => $param2,
            'param3' => $param3
        ]), true);
    }

    




    public function contact_delete($id)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/contact/delete', [
            'id' => $id
        ]), true);
    }

    




    public function blacklist_add($number)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/blacklist/add', [
            is_array($number) ? 'numbers' : 'number' => $number
        ]), true);
    }

    




    public function blacklist_delete($id)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/blacklist/delete', [
            'id' => $id
        ]), true);
    }


    




    public function hlr_check($number)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/hlr/check', [
            is_array($number) ? 'numbers' : 'number' => $number
        ]), true);
    }

    




    public function hlr_status($id)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/hlr/status', [
            'id' => $id
        ]), true);
    }

    




    public function number_operator($number)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/number/operator', [
            is_array($number) ? 'numbers' : 'number' => $number
        ]), true);
    }

    


















    public function viber_send($number, $groupId, $sign, $channel, $text, $imageSource = null, $textButton = null, $linkButton = null, $dateSend = null, $signSms = null, $channelSms = null, $textSms = null, $priceSms = null)
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/viber/send', [
            is_array($number) && !empty($number) ? 'numbers' : 'number' => $number,
            'groupId' => $groupId,
            'sign' => $sign,
            'channel' => $channel,
            'text' => $text,
            '$imageSource' => $imageSource,
            'textButton' => $textButton,
            'linkButton' => $linkButton,
            'dateSend' => $dateSend,
            'signSms' => $signSms,
            'channelSms' => $channelSms,
            'textSms' => $textSms,
            'priceSms' => $priceSms
        ]), true);
    }

    



    public function viber_list()
    {
        return json_decode(self::curl_post(self::URL_SMSAERO_API . '/viber/list', []), true);
    }
}

