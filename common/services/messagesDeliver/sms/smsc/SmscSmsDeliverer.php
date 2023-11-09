<?php

namespace common\services\messagesDeliver\sms\smsc;

use common\services\messagesDeliver\sms\SmsDeliverer;
use Yii;





class SmscSmsDeliverer extends SmsDeliverer
{
    public const SMSC_LOGIN_ENV = 'SMSC_LOGIN';
    public const SMSC_PASSWORD_ENV = 'SMSC_PASSWORD';
    
    public const SMSC_HTTPS_ENV = 'SMSC_HTTPS';
    public const SMSC_DEBUG_ENV = 'SMSC_DEBUG';

    public string $login;
    public string $password;
    public bool $use_post;
    public bool $use_https;
    public string $charset;
    public bool $debug;

    public function deliverMessage(string $title, string $message): array
    {
        if (!$this->isConfigured()) {
            return [false, 'Параметры SMS-шлюза не настроен'];
        }
        try {
            $result = $this->send_sms($this->recipient->getRecipientAddress(), "$title\n$message");
        } catch (\Throwable $e) {
            Yii::error("Не удалось отправить СМС на номер {$this->recipient->getRecipientAddress()} по причине: {$e->getMessage()}");
            return [false, $e->getMessage()];
        }
        if (isset($result[1]) && $result[1] > 0) {
            return [true, null];
        } else {
            $result = json_encode($result);
            Yii::error("Не удалось отправить СМС на номер {$this->recipient->getRecipientAddress()} по причине: {$result}");
            return [false, $result];
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->login) && !empty($this->password);
    }
    
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    private function send_sms($phones, $message, $translit = 0, $time = 0, $id = 0, $format = 0, $sender = false, $query = "", $files = array())
    {
        static $formats = array(1 => "flash=1", "push=1", "hlr=1", "bin=1", "bin=2", "ping=1", "mms=1", "mail=1", "call=1", "viber=1", "soc=1");

        $m = $this->_smsc_send_cmd("send", "cost=3&phones=" . urlencode($phones) . "&mes=" . urlencode($message) .
            "&translit=$translit&id=$id" . ($format > 0 ? "&" . $formats[$format] : "") .
            ($sender === false ? "" : "&sender=" . urlencode($sender)) .
            ($time ? "&time=" . urlencode($time) : "") . ($query ? "&$query" : ""), $files);

        

        if ($this->debug) {
            if ($m[1] > 0)
                Yii::debug("Сообщение отправлено успешно. ID: $m[0], всего SMS: $m[1], стоимость: $m[2], баланс: $m[3].\n");
            else
                Yii::debug("Ошибка №" . -$m[1] . ($m[0] ? ", ID: " . $m[0] : "") . "\n");
        }

        return $m;
    }


    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    private function get_sms_cost($phones, $message, $translit = 0, $format = 0, $sender = false, $query = "")
    {
        static $formats = array(1 => "flash=1", "push=1", "hlr=1", "bin=1", "bin=2", "ping=1", "mms=1", "mail=1", "call=1", "viber=1", "soc=1");

        $m = $this->_smsc_send_cmd("send", "cost=1&phones=" . urlencode($phones) . "&mes=" . urlencode($message) .
            ($sender === false ? "" : "&sender=" . urlencode($sender)) .
            "&translit=$translit" . ($format > 0 ? "&" . $formats[$format] : "") . ($query ? "&$query" : ""));

        

        if ($this->debug) {
            if ($m[1] > 0)
                Yii::debug("Стоимость рассылки: $m[0]. Всего SMS: $m[1]\n");
            else
                Yii::debug("Ошибка №" . -$m[1] . "\n");
        }

        return $m;
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    private function get_status($id, $phone, $all = 0)
    {
        $m = $this->_smsc_send_cmd("status", "phone=" . urlencode($phone) . "&id=" . urlencode($id) . "&all=" . (int)$all);

        

        if (!strpos($id, ",")) {
            if ($this->debug)
                if ($m[1] != "" && $m[1] >= 0)
                    Yii::debug("Статус SMS = $m[0]" . $m[1] ? ", время изменения статуса - " . date("d.m.Y H:i:s", $m[1]) : "" . "\n");
                else
                    Yii::debug("Ошибка №" . -$m[1] . "\n");

            if ($all && count($m) > 9 && (!isset($m[$idx = $all == 1 ? 14 : 17]) || $m[$idx] != "HLR")) 
                $m = explode(",", implode(",", $m), $all == 1 ? 9 : 12);
        } else {
            if (count($m) == 1 && strpos($m[0], "-") == 2)
                return explode(",", $m[0]);

            foreach ($m as $k => $v)
                $m[$k] = explode(",", $v);
        }

        return $m;
    }

    
    
    
    
    
    private function get_balance()
    {
        $m = $this->_smsc_send_cmd("balance"); 

        if ($this->debug) {
            if (!isset($m[1]))
                Yii::debug("Сумма на счете: " . $m[0] . "\n");
            else
                Yii::debug("Ошибка №" . -$m[1] . "\n");
        }

        return isset($m[1]) ? false : $m[0];
    }


    

    
    private function _smsc_send_cmd($cmd, $arg = "", $files = array())
    {
        $url = $_url = ($this->use_https ? "https" : "http") . "://smsc.ru/sys/$cmd.php?login=" . urlencode($this->login) . "&psw=" . urlencode($this->password) . "&fmt=1&charset=" . $this->charset . "&" . $arg;

        $i = 0;
        do {
            if ($i++)
                $url = str_replace('://smsc.ru/', '://www' . $i . '.smsc.ru/', $_url);

            $ret = $this->_smsc_read_url($url, $files, 3 + $i);
        } while ($ret == "" && $i < 5);

        if ($ret == "") {
            if ($this->debug)
                Yii::debug("Ошибка чтения адреса: $url\n");

            $ret = ","; 
        }

        $delim = ",";

        if ($cmd == "status") {
            parse_str($arg, $m);

            if (strpos($m["id"], ","))
                $delim = "\n";
        }

        return explode($delim, $ret);
    }

    
    
    private function _smsc_read_url($url, $files, $tm = 5)
    {
        $ret = "";
        $post = $this->use_post || strlen($url) > 2000 || $files;

        if (function_exists("curl_init")) {
            static $c = 0; 

            if (!$c) {
                $c = curl_init();
                curl_setopt_array($c, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_CONNECTTIMEOUT => $tm,
                    CURLOPT_TIMEOUT => 60,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTPHEADER => array("Expect:")
                ));
            }

            curl_setopt($c, CURLOPT_POST, $post);

            if ($post) {
                list($url, $post) = explode("?", $url, 2);

                if ($files) {
                    parse_str($post, $m);

                    foreach ($m as $k => $v)
                        $m[$k] = isset($v[0]) && $v[0] == "@" ? sprintf("\0%s", $v) : $v;

                    $post = $m;
                    foreach ($files as $i => $path)
                        if (file_exists($path))
                            $post["file" . $i] = function_exists("curl_file_create") ? curl_file_create($path) : "@" . $path;
                }

                curl_setopt($c, CURLOPT_POSTFIELDS, $post);
            }

            curl_setopt($c, CURLOPT_URL, $url);

            $ret = curl_exec($c);
        } elseif ($files) {
            if ($this->debug)
                Yii::debug("Не установлен модуль curl для передачи файлов\n");
        } else {
            if (!$this->use_https && function_exists("fsockopen")) {
                $m = parse_url($url);

                if (!$fp = fsockopen($m["host"], 80, $errno, $errstr, $tm))
                    $fp = fsockopen("212.24.33.196", 80, $errno, $errstr, $tm);

                if ($fp) {
                    stream_set_timeout($fp, 60);

                    fwrite($fp, ($post ? "POST $m[path]" : "GET $m[path]?$m[query]") . " HTTP/1.1\r\nHost: smsc.ru\r\nUser-Agent: PHP" . ($post ? "\r\nContent-Type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($m['query']) : "") . "\r\nConnection: Close\r\n\r\n" . ($post ? $m['query'] : ""));

                    while (!feof($fp))
                        $ret .= fgets($fp, 1024);
                    list(, $ret) = explode("\r\n\r\n", $ret, 2);

                    fclose($fp);
                }
            } else
                $ret = file_get_contents($url);
        }

        return $ret;
    }
}