<?php

namespace common\services\messagesDeliver\sms\smsRu;


use stdClass;





class SMSRU
{

    private $ApiKey;
    private $protocol = 'https';
    private $domain = 'sms.ru';
    private $count_repeat = 5;    


    function __construct($ApiKey)
    {
        $this->ApiKey = $ApiKey;
    }

    












    public function send_one($post)
    {
        $url = $this->protocol . '://' . $this->domain . '/sms/send';
        $request = $this->Request($url, $post);
        $resp = $this->CheckReplyError($request, 'send');

        if ($resp->status == "OK") {
            $temp = (array)$resp->sms;
            unset($resp->sms);

            $temp = array_pop($temp);

            if ($temp) {
                return $temp;
            } else {
                return $resp;
            }
        } else {
            return $resp;
        }

    }

    public function send($post)
    {
        $url = $this->protocol . '://' . $this->domain . '/sms/send';
        $request = $this->Request($url, $post);
        return $this->CheckReplyError($request, 'send');
    }

    









    public function sendSmtp($post)
    {
        $post->to = $this->ApiKey . '@' . $this->domain;
        $post->subject = $this->sms_mime_header_encode($post->subject, $post->charset, $post->send_charset);
        if ($post->charset != $post->send_charset) {
            $post->body = iconv($post->charset, $post->send_charset, $post->body);
        }
        $headers = "From: $post->\r\n";
        $headers .= "Content-type: text/plain; charset=$post->send_charset\r\n";
        return mail($post->to, $post->subject, $post->body, $headers);
    }

    public function getStatus($id)
    {
        $url = $this->protocol . '://' . $this->domain . '/sms/status';

        $post = new stdClass();
        $post->sms_id = $id;

        $request = $this->Request($url, $post);
        return $this->CheckReplyError($request, 'getStatus');
    }

    







    public function getCost($post)
    {
        $url = $this->protocol . '://' . $this->domain . '/sms/cost';
        $request = $this->Request($url, $post);
        return $this->CheckReplyError($request, 'getCost');
    }

    


    public function getBalance()
    {
        $url = $this->protocol . '://' . $this->domain . '/my/balance';
        $request = $this->Request($url);
        return $this->CheckReplyError($request, 'getBalance');
    }

    


    public function getLimit()
    {
        $url = $this->protocol . '://' . $this->domain . '/my/limit';
        $request = $this->Request($url);
        return $this->CheckReplyError($request, 'getLimit');
    }

    


    public function getSenders()
    {
        $url = $this->protocol . '://' . $this->domain . '/my/senders';
        $request = $this->Request($url);
        return $this->CheckReplyError($request, 'getSenders');
    }

    





    public function addStopList($phone, $text = "")
    {
        $url = $this->protocol . '://' . $this->domain . '/stoplist/add';

        $post = new stdClass();
        $post->stoplist_phone = $phone;
        $post->stoplist_text = $text;

        $request = $this->Request($url, $post);
        return $this->CheckReplyError($request, 'addStopList');
    }

    




    public function delStopList($phone)
    {
        $url = $this->protocol . '://' . $this->domain . '/stoplist/del';

        $post = new stdClass();
        $post->stoplist_phone = $phone;

        $request = $this->Request($url, $post);
        return $this->CheckReplyError($request, 'delStopList');
    }

    


    public function getStopList()
    {
        $url = $this->protocol . '://' . $this->domain . '/stoplist/get';
        $request = $this->Request($url);
        return $this->CheckReplyError($request, 'getStopList');
    }

    





    public function addCallback($post)
    {
        $url = $this->protocol . '://' . $this->domain . '/callback/add';
        $request = $this->Request($url, $post);
        return $this->CheckReplyError($request, 'addCallback');
    }

    





    public function delCallback($post)
    {
        $url = $this->protocol . '://' . $this->domain . '/callback/del';
        $request = $this->Request($url, $post);
        return $this->CheckReplyError($request, 'delCallback');
    }

    


    public function getCallback()
    {
        $url = $this->protocol . '://' . $this->domain . '/callback/get';
        $request = $this->Request($url);
        return $this->CheckReplyError($request, 'getCallback');
    }

    private function Request($url, $post = FALSE)
    {
        if ($post) {
            $r_post = $post;
        }
        $ch = curl_init($url . "?json=1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        if (!$post) {
            $post = new stdClass();
        }

        if (!empty($post->api_id) && $post->api_id == 'none') {
        } else {
            $post->api_id = $this->ApiKey;
        }

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query((array)$post));

        $body = curl_exec($ch);
        if ($body === FALSE) {
            $error = curl_error($ch);
        } else {
            $error = FALSE;
        }
        curl_close($ch);
        if ($error && $this->count_repeat > 0) {
            $this->count_repeat--;
            return $this->Request($url, $r_post);
        }
        return $body;
    }

    private function CheckReplyError($res, $action)
    {

        if (!$res) {
            $temp = new stdClass();
            $temp->status = "ERROR";
            $temp->status_code = "000";
            $temp->status_text = "Невозможно установить связь с сервером SMS.RU. Проверьте - правильно ли указаны DNS сервера в настройках вашего сервера (nslookup sms.ru), и есть ли связь с интернетом (ping sms.ru).";
            return $temp;
        }

        $result = json_decode($res);

        if (!$result || !$result->status) {
            $temp = new stdClass();
            $temp->status = "ERROR";
            $temp->status_code = "000";
            $temp->status_text = "Невозможно установить связь с сервером SMS.RU. Проверьте - правильно ли указаны DNS сервера в настройках вашего сервера (nslookup sms.ru), и есть ли связь с интернетом (ping sms.ru)";
            return $temp;
        }

        return $result;
    }

    private function sms_mime_header_encode($str, $post_charset, $send_charset)
    {
        if ($post_charset != $send_charset) {
            $str = iconv($post_charset, $send_charset, $str);
        }
        return "=?" . $send_charset . "?B?" . base64_encode($str) . "?=";
    }
}
