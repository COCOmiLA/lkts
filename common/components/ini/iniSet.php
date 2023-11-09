<?php

namespace common\components\ini;

class iniSet
{
    public static function disableTimeLimit()
    {
        set_time_limit(0);
        ini_set("default_socket_timeout", -1);
    }

    public static function extendMemoryLimit($value = '4096M')
    {
        ini_set('memory_limit', $value);
    }
}