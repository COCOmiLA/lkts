<?php

function getRequirementChecker($frameworkPath = '', $phppath = '')
{
    require_once($frameworkPath . '/YiiRequirementChecker.php');
    $requirementsChecker = new YiiRequirementChecker();

    $requirements = [
        
        [
            'name' => 'Расширение PDO PHP',
            'mandatory' => true,
            'condition' => extension_loaded('pdo'),
            'by' => 'База данных',
        ],
        [
            'name' => 'php7-session',
            'mandatory' => true,
            'condition' => is_callable('session_start'),
            'by' => 'Необходимо для работы с сессией',
        ],
        [
            'name' => 'PDO MySQL PHP extension',
            'mandatory' => true,
            'condition' => extension_loaded('pdo_mysql'),
            'by' => 'База данных',
            'memo' => 'Необходимо для работы с базой данных MySQL database',
        ],
        [
            'name' => 'Intl PHP extension',
            'mandatory' => true,
            'condition' => extension_loaded('intl'),
            'by' => 'Internationalization Functions',
            'memo' => ''
        ],
        [
            'name' => 'Fileinfo PHP extension',
            'mandatory' => true,
            'condition' => extension_loaded('fileinfo'),
            'by' => 'Fileinfo Functions',
            'memo' => 'Получение данных о файлах'
        ],
        [
            'name' => 'Soap client PHP extension',
            'mandatory' => true,
            'condition' => extension_loaded('soap'),
            'by' => 'SOAP Functions',
            'memo' => 'связь с SOAP сервисами'
        ],
        [
            'name' => 'Mbstring PHP extension',
            'mandatory' => true,
            'condition' => extension_loaded('mbstring'),
            'by' => 'Multibyte strings',
            'memo' => 'Работа со строками'
        ],
        [
            'name' => 'GD PHP extension',
            'mandatory' => true,
            'condition' => extension_loaded('gd'),
            'by' => 'Work with images',
            'memo' => 'Работа с изображениями'
        ],
        [
            'name' => 'Curl PHP extension',
            'mandatory' => true,
            'condition' => extension_loaded('curl'),
            'by' => 'Curl Functions',
            'memo' => ''
        ],
        [
            'name' => 'XML PHP extension',
            'mandatory' => true,
            'condition' => extension_loaded('xml'),
            'by' => 'XML Functions',
            'memo' => ''
        ],
        'phpExposePhp' => [
            'name' => 'Expose PHP',
            'mandatory' => false,
            'condition' => $requirementsChecker->checkPhpIniOff("expose_php"),
            'by' => 'Security reasons',
            'memo' => 'опция "expose_php" должна быть отключена в php.ini',
        ],
        'phpAllowUrlInclude' => [
            'name' => 'PHP allow url include',
            'mandatory' => false,
            'condition' => $requirementsChecker->checkPhpIniOff("allow_url_include"),
            'by' => 'Security reasons',
            'memo' => 'опция "allow_url_include" должна быть отключена в php.ini',
        ],
        'phpSmtp' => [
            'name' => 'PHP mail SMTP',
            'mandatory' => false,
            'condition' => strlen(ini_get('SMTP')) > 0,
            'by' => 'Email sending',
            'memo' => 'работа с почтой',
        ],
        'ZipArchive' => [
            'name' => 'PHP zip archive',
            'mandatory' => false,
            'condition' => class_exists('ZipArchive'),
            'by' => 'Zip archive',
            'memo' => 'работа с zip-архивами',
        ],
        'phpPath' => [
            'name' => 'Path to php.exe is indicated',
            'mandatory' => false,
            'condition' => $requirementsChecker->checkPhpPath($phppath),
            'by' => 'Working on php',
            'memo' => 'Путь к исполняемому файлу php должен быть указан в системе',
        ],
        'phpTimezone' => [
            'name' => 'PHP Timezone',
            'mandatory' => true,
            'condition' => check_timezone(date_default_timezone_get()),
            'by' => 'Working on php',
            'memo' => 'Настройка таймзоны в системе',
        ],
        'check_upload_tmp_dir ' => [
            'name' => 'Check upload_tmp_dir',
            'mandatory' => false,
            'condition' => check_upload_tmp_dir(),
            'by' => 'upload_tmp_dir is available',
            'memo' => 'Проверка доступности upload_tmp_dir',
        ]
    ];
    return $requirementsChecker->checkYii()->check($requirements);
}

function check_timezone($tz = '')
{
    if (empty($tz)) {
        return false;
    }

    $parts = explode('/', $tz);

    if (sizeof($parts) === 2) {
        $region = $parts[0];
        $country = $parts[1];

        $new_region = ucfirst(strtolower($region));
        $new_country = ucfirst(strtolower($country));

        if ($new_region === $region && $new_country === $country) {
            return true;
        } else {
            return false;
        }
    } else {
        return true;
    }
}

function check_upload_tmp_dir()
{
    $upload_tmp_dir = ini_get('upload_tmp_dir');
    if (empty($upload_tmp_dir)) {
        return true;
    }
    if (!is_dir($upload_tmp_dir)) {
        return false;
    }
    if (!is_writable($upload_tmp_dir)) {
        return false;
    }
    return true;
}