<?php

namespace common\models\logs;

class LogVarsExcluder
{
    public static function excludeVars(): array
    {
        return [
            '_SERVER.HTTP_AUTHORIZATION',
            '_SERVER.PHP_AUTH_USER',
            '_SERVER.PHP_AUTH_PW',
            '_SERVER.DB_USERNAME',
            '_SERVER.DB_PASSWORD',
            '_SERVER.MAIL_PASSWORD',
            '_SERVER.ABIT_PASSWORD',
            '_SERVER.STUDENT_PASSWORD',
            '_SERVER.WEB_APP_PASSWORD',
            '_SERVER.SITE_KEY',
            '_SERVER.SERVER_KEY',
            '_SERVER.SITE_KEY_V2',
            '_SERVER.SERVER_KEY_V2',
            '_SERVER.SITE_KEY_V3',
            '_SERVER.SERVER_KEY_V3',
        ];
    }
}