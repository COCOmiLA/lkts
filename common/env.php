<?php



$dotenv = null;
if (file_exists(dirname(__DIR__) . '/.env')) {
    $dotenv = new \Dotenv\Dotenv(dirname(__DIR__));
} else if (defined('PORTAL_CONSOLE_INSTALLATION')) {
    
    putenv('DB_DSN=mysql:host=localhost;port=3306;dbname=portal');
} else {
    
    print_r('Ошибка. Отсутствует .env файл.');
    die();
}
if ($dotenv) {
    $dotenv->load();
}

defined('YII_DEBUG') or define('YII_DEBUG', getenv('YII_DEBUG') === 'true');
defined('YII_ENV') or define('YII_ENV', getenv('YII_ENV') ?: 'prod');
