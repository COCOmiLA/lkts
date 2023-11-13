<?php








$requirements = [
    [
        'name' => 'PHP version',
        'mandatory' => true,
        'condition' => (version_compare(PHP_VERSION, '7.3', '>') && version_compare(PHP_VERSION, '8.0', '<')),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
        'memo' => 'Версия PHP 7.4',
    ],
    [
        'name' => 'Reflection extension',
        'mandatory' => true,
        'condition' => class_exists('Reflection', false),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
    ],
    [
        'name' => 'PCRE extension',
        'mandatory' => true,
        'condition' => extension_loaded('pcre'),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
    ],
    [
        'name' => 'CURL extension',
        'mandatory' => true,
        'condition' => extension_loaded('curl'),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
    ],
    [
        'name' => 'SPL extension',
        'mandatory' => true,
        'condition' => extension_loaded('SPL'),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>',
    ],
    [
        'name' => 'Ctype extension',
        'mandatory' => true,
        'condition' => extension_loaded('ctype'),
        'by' => '<a href="http://www.yiiframework.com">Yii Framework</a>'
    ],
    [
        'name' => 'OpenSSL PHP extension',
        'mandatory' => false,
        'condition' => extension_loaded('openssl'),
        'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-base-security.html">Security Component</a>',
        'memo' => ''
    ],
    [
        'name' => 'ICU version',
        'mandatory' => false,
        'condition' => defined('INTL_ICU_VERSION') && version_compare(INTL_ICU_VERSION, '49', '>='),
        'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
        'memo' => 'ICU 49.0 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href=\"http://www.yiiframework.com/doc-2.0/yii-i18n-formatter.html#asRelativeTime%28%29-detail\">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU version is ' .
            (defined('INTL_ICU_VERSION') ? INTL_ICU_VERSION : '(ICU is missing)') . '.'
    ],
    [
        'name' => 'ICU Data version',
        'mandatory' => false,
        'condition' => defined('INTL_ICU_DATA_VERSION') && version_compare(INTL_ICU_DATA_VERSION, '49.1', '>='),
        'by' => '<a href="http://www.php.net/manual/en/book.intl.php">Internationalization</a> support',
        'memo' => 'ICU Data 49.1 or higher is required when you want to use <code>#</code> placeholder in plural rules
        (for example, plural in
        <a href=\"http://www.yiiframework.com/doc-2.0/yii-i18n-formatter.html#asRelativeTime%28%29-detail\">
        Formatter::asRelativeTime()</a>) in the <code>yii\i18n\Formatter</code> class. Your current ICU Data version is ' .
            (defined('INTL_ICU_DATA_VERSION') ? INTL_ICU_DATA_VERSION : '(ICU Data is missing)') . '.'
    ],
    [
        'name' => 'DOM extension',
        'mandatory' => false,
        'condition' => extension_loaded('dom'),
        'by' => '<a href="http://php.net/manual/en/book.dom.php">Document Object Model</a>',
        'memo' => ''
    ],
];

if (key_exists('SERVER_PROTOCOL', $_SERVER)) {
    $protocol = strpos($_SERVER['SERVER_PROTOCOL'], '/');
    $protocol = substr($_SERVER['SERVER_PROTOCOL'], 0, $protocol);
    try {
        $test_connection = (bool) file_get_contents("{$protocol}://{$_SERVER['HTTP_HOST']}/frontend/web/test_connection.php");
    } catch (\Throwable $th) {
        $test_connection = false;
    }

    $requirements[] = [
        'name' => 'Test connection',
        'mandatory' => false,
        'condition' => $test_connection,
        'by' => '<a href="http://php.net/manual/en/book.dom.php">Document Object Model</a>',
        'memo' => ''
    ];
}

return $requirements;
