<?php

namespace console\controllers;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;




class InstallLkController extends Controller
{
    


    public $check_environments;

    


    public $php_path;

    


    public $db_type;
    


    public $db_host;

    


    public $db_port;

    


    public $db_username;

    


    public $db_password;

    


    public $db_name;

    


    public $db_silent_setup;

    


    public $domain;

    


    public $portal_name;

    


    public $enable_1c_services;

    


    public $odinc_url;

    


    public $odinc_url_stud;

    


    public $odinc_url_abit;

    


    public $odinc_login_stud;

    


    public $odinc_login_abit;

    


    public $odinc_password_stud;

    


    public $odinc_password_abit;

    







    public $server_type;

    public $help = false;

    private $rootPath = '';
    private $stepNumber = 1;

    const YES_LIST = [
        'y',
        'д',
        'да',
        'yes',
    ];

    const NO_LIST = [
        'н',
        'n',
        'no',
        'нет',
    ];

    const SERVER_OTHER = 0; 
    const SERVER_APACHE = 1;
    const SERVER_IIS = 2;

    const SERVERS_LIST = [
        self::SERVER_IIS => 'IIS',
        self::SERVER_APACHE => 'Apache',
        self::SERVER_OTHER => 'Другой (nginx и др.)',
    ];

    public function options($actionID)
    {
        return [
            'help',
            'domain',
            'db_type',
            'db_host',
            'db_port',
            'db_name',
            'php_path',
            'odinc_url',
            'db_username',
            'db_password',
            'server_type',
            'portal_name',
            'odinc_url_stud',
            'odinc_url_abit',
            'db_silent_setup',
            'odinc_login_stud',
            'odinc_login_abit',
            'check_environments',
            'enable_1c_services',
            'odinc_password_stud',
            'odinc_password_abit',
        ];
    }

    public function optionAliases()
    {
        return [
            'h' => 'help',
            'd' => 'domain',
            'dt' => 'db_type',
            'dh' => 'db_host',
            'dp' => 'db_port',
            'dn' => 'db_name',
            'pp' => 'php_path',
            'ou' => 'odinc_url',
            'du' => 'db_username',
            'st' => 'server_type',
            'pn' => 'portal_name',
            'dpw' => 'db_password',
            'ds' => 'db_silent_setup',
            'ous' => 'odinc_url_stud',
            'oua' => 'odinc_url_abit',
            'ols' => 'odinc_login_stud',
            'ola' => 'odinc_login_abit',
            'ce' => 'check_environments',
            'es' => 'enable_1c_services',
            'ops' => 'odinc_password_stud',
            'opa' => 'odinc_password_abit',
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->check_environments = $this->convertToBoolean($this->check_environments, true);
        if (!isset($this->check_environments)) {
            return false;
        }

        $this->db_silent_setup = $this->convertToBoolean($this->db_silent_setup, false);
        if (!isset($this->db_silent_setup)) {
            return false;
        }

        if (empty($this->php_path)) {
            $this->php_path = 'php';
        }

        if (empty($this->db_name)) {
            $this->db_name = 'lk_db';
        }

        if (empty($this->domain)) {
            $this->domain = 'localhost';
        }

        if (empty($this->db_password)) {
            $this->db_password = '';
        }

        if (empty($this->db_type)) {
            $this->db_type = 'mysql';
        }

        if (empty($this->db_port)) {
            $this->db_port = '3306';
            if ($this->db_type == 'pgsql') {
                $this->db_port = '5432';
            }
        }

        if (empty($this->db_host)) {
            $this->db_host = 'localhost';
        }

        if (empty($this->db_username)) {
            $this->db_username = 'root';
        }

        if (empty($this->portal_name)) {
            $this->portal_name = '1c portal';
        }

        if (empty($this->odinc_password_stud)) {
            $this->odinc_password_stud = '1';
        }

        if (empty($this->odinc_password_abit)) {
            $this->odinc_password_abit = '1';
        }

        if (empty($this->odinc_url)) {
            $this->odinc_url = 'http://localhost/local';
        }

        if (empty($this->odinc_url_stud)) {
            $this->odinc_url_stud = '/ws/Study.1cws?wsdl';
        }

        if (empty($this->odinc_url_abit)) {
            $this->odinc_url_abit = '/ws/webabit.1cws?wsdl';
        }

        if (empty($this->odinc_login_abit)) {
            $this->odinc_login_abit = 'Пользователь Web-сервис WebAbit';
        }

        if (empty($this->odinc_login_stud)) {
            $this->odinc_login_stud = 'Пользователь Web-сервис WebStudy';
        }

        if (isset($this->server_type)) {
            $this->server_type = (int)$this->server_type;
        }

        $this->rootPath = FileHelper::normalizePath(dirname(__FILE__) . '/../..');

        return true;
    }

    public function actionIndex()
    {
        if ($this->check_environments) {
            if (!$this->environmentsIsCorrect()) {
                $this->printError('Система не удовлетворяет требованиям');
                return ExitCode::UNSPECIFIED_ERROR;
            }

            if (!$this->foldersPermissionIsCorrect()) {
                $this->printError('Система не удовлетворяет требованиям');
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }

        if (!$this->setServerSettings()) {
            $this->printError('Не удалось настроить конфигурацию сервера');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$this->setDataBaseSettings()) {
            $this->printError('Не удалось настроить конфигурацию сервера СУБД');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$this->setEnvironment()) {
            $this->printError('Не удалось настроить .env сервера');
            return ExitCode::UNSPECIFIED_ERROR;
        }

        if (!$this->installEnding()) {
            $this->printError('Не удалось завершить установку');
            return ExitCode::UNSPECIFIED_ERROR;
        }
        return ExitCode::OK;
    }

    public function printNextStepNumber()
    {
        echo 'Шаг ' .
            $this->ansiFormat('№', Console::FG_CYAN) .
            $this->ansiFormat($this->stepNumber, Console::FG_YELLOW) .
            "\n";

        $this->stepNumber++;
    }

    public function actionVerify()
    {
        echo Table::widget([
            'headers' => ['Project', 'Status'],
            'rows' => [
                ['Portal 1C', 'OK'],
            ],
        ]);

        return ExitCode::OK;
    }

    





    private function convertToBoolean($param = '', $default = false)
    {
        if (empty($param)) {
            return $default;
        }

        $param = mb_strtolower($param);
        if (in_array($param, self::YES_LIST)) {
            return true;
        } elseif (in_array($param, self::NO_LIST)) {
            return false;
        } else {
            $this->printError('Неверно указанный параметр.');

            return null;
        }
    }

    


    private function printError($message = '')
    {
        $message = $this->ansiFormat(
            $message,
            Console::FG_RED
        );
        $this->stdout(
            "Ошибка. {$message}",
            Console::BOLD
        );
    }

    


    private function foldersPermissionIsCorrect()
    {
        $this->printNextStepNumber();

        
        
        $path = FileHelper::normalizePath("{$this->rootPath}/frontend/web/install/src/steps/webserver/");

        require_once("{$path}/folders_requirements.php");

        $folders = getFolders();

        $table = [
            'headers' => ['№', 'Директория', 'Права'],
            'rows' => [],
        ];
        $hasError = false;
        $i = 1;
        foreach ($folders as $dir => $permission) {
            if ($permission) {
                $status = '× некорректные права ×';
                $hasError = true;
            } else {
                $status = '✓ права корректные ✓';
            }

            $table['rows'][] = [
                $i,
                $dir,
                $status,
            ];
            $i++;
        }

        echo 'Проверка ' . $this->ansiFormat('прав доступа', Console::FG_YELLOW) . "\n";
        echo Table::widget($table);
        $finalWord = $this->ansiFormat('СООТВЕТСТВУЮТ', Console::FG_GREEN);
        if ($hasError) {
            $finalWord = $this->ansiFormat('НЕ СООТВЕТСТВУЮТ', Console::FG_RED);
        }
        echo "Директории {$finalWord} правам доступа\n\n";

        return !$hasError;
    }

    


    private function environmentsIsCorrect()
    {
        $this->printNextStepNumber();

        
        
        $path = FileHelper::normalizePath("{$this->rootPath}/frontend/web/install/src/steps/system_requirements");
        $frameworkPath = FileHelper::normalizePath("{$path}/requirements");
        if (!is_dir($frameworkPath)) {
            $this->printError('Невозможно найти Yii фреймворк');
            return false;
        }

        require_once("{$path}/customs_requirements.php");

        $requirementsChecker = getRequirementChecker($frameworkPath, $this->php_path);

        if (empty($requirementsChecker->result['requirements'])) {
            return true;
        }

        $table = [
            'headers' => ['№', 'Результат', 'Наименование', 'Описание'],
            'rows' => [],
        ];
        $hasError = false;
        foreach ($requirementsChecker->result['requirements'] as $key => $requirement) {
            if ($requirement['error']) {
                $status = '× ошибка ×';
                $hasError = true;
            } elseif ($requirement['warning']) {
                $status = '! внимание !';
            } else {
                $status = '✓ успех ✓';
            }

            $memo = $requirement['memo'];
            if (strlen((string)$memo) > 115) {
                $memo = '-';
            }

            $table['rows'][] = [
                $key,
                $status,
                $requirement['name'],
                $memo,
            ];
        }

        echo 'Проверка корректности ' . $this->ansiFormat('окружения', Console::FG_YELLOW) . "\n";
        echo Table::widget($table);
        $finalWord = $this->ansiFormat('СООТВЕТСТВУЕТ', Console::FG_GREEN);
        if ($hasError) {
            $finalWord = $this->ansiFormat('НЕ СООТВЕТСТВУЕТ', Console::FG_RED);
        }
        echo "Окружение {$finalWord} минимальным требованиям\n\n";

        return !$hasError;
    }

    


    private function setServerSettings()
    {
        $this->printNextStepNumber();

        if (!isset($this->server_type)) {
            echo "Выберите используемый веб-сервер:\n";
            foreach (self::SERVERS_LIST as $number => $name) {
                echo "  {$number}) $name\n";
            }
            $this->server_type = (int)readline('');
        }

        $server = ArrayHelper::getValue(self::SERVERS_LIST, $this->server_type);
        if (isset($server)) {
            $server = $this->ansiFormat($server, Console::FG_YELLOW);
            echo "Копирование конфигурации для сервера \"{$server}\"\n";

            $confsPath = FileHelper::normalizePath("{$this->rootPath}/confs");

            $copyResult = false;
            
            switch ($this->server_type) {
                case self::SERVER_APACHE:
                    
                    copy("{$confsPath}/.htaccess", "{$this->rootPath}/.htaccess");
                    $copyResult = file_exists("{$this->rootPath}/.htaccess");
                    break;

                case self::SERVER_IIS:
                    
                    copy("{$confsPath}/web.config", "{$this->rootPath}/web.config");
                    $copyResult = file_exists("{$this->rootPath}/web.config");
                    break;

                case self::SERVER_OTHER:
                    $copyResult = true;
                    break;

            }
            if ($copyResult) {
                echo $this->ansiFormat("Конфигурация успешно скопирована\n\n", Console::FG_GREEN);
            } else {
                echo $this->ansiFormat("Произошла ошибка копирования\n\n", Console::FG_RED);
            }

            return $copyResult;
        } else {
            $errorType = $this->ansiFormat('НЕ ВЕРНЫЙ', Console::FG_RED);
            echo "Выбран {$errorType} тип сервера\n\n";
        }

        return false;
    }

    


    private function setDataBaseSettings()
    {
        $this->printNextStepNumber();

        if (!$this->db_silent_setup) {
            $attrList = [
                'db_type' => 'Тип СУБД (mysql/pgsql)',
                'db_host' => 'Адрес сервера',
                'db_port' => 'Порт сервера',
                'db_username' => 'Имя пользователя',
                'db_password' => 'Пароль',
                'db_name' => 'Наименование базы данных',
            ];
            echo "Введите параметры для подключения к серверу СУБД (значение в скобках будет задано при пропуске параметра)\n";

            foreach ($attrList as $attr => $alias) {
                $buffer = (string)readline("  {$alias} ({$this->{$attr}}): ");
                $buffer = trim((string)$buffer);
                if (!empty($buffer)) {
                    $this->{$attr} = $buffer;
                }
            }
            echo "\n";
        }

        if (preg_match('/[^a-zA-Z0-9]+^_/', $this->db_name)) {
            $this->printError('В названии БД используются недопустимый символ');
            return false;
        }

        $serverSettings = $this->ansiFormat(
            "{$this->db_type}:host={$this->db_host} port={$this->db_port} username={$this->db_username} db_name={$this->db_name}",
            Console::FG_YELLOW
        );
        echo "Настройка сервера СУБД: \"{$serverSettings}\".\n";

        
        
        $path = FileHelper::normalizePath("{$this->rootPath}/frontend/web/install/src/controllers/DatabaseController.php");
        require_once($path);
        $className = 'DatabaseController';

        $databaseController = new $className();
        $databaseController->type = $this->db_type;
        $databaseController->port = $this->db_port;
        $databaseController->server = $this->db_host;
        $databaseController->dbname = $this->db_name;
        $databaseController->username = $this->db_username;
        $databaseController->password = $this->db_password;

        $connectResult = $databaseController->connectToServer(false);

        if ($connectResult === true) {
            $useDbResult = $databaseController->useDb(false);

            if ($useDbResult === true) {
                $writeDsnToEnvResult = $databaseController->writeDsnToEnv('/..', false);

                if ($writeDsnToEnvResult === true) {
                    echo $this->ansiFormat("Cервер СУБД успешно настроен\n\n", Console::FG_GREEN);
                    return true;
                } else {
                    $this->printError("{$writeDsnToEnvResult}\n");
                    return false;
                }
            } else {
                $this->printError("{$useDbResult}\n");
                return false;
            }
        } else {
            $this->printError("{$connectResult}\n");
        }

        return false;
    }

    


    private function setEnvironment()
    {
        $this->printNextStepNumber();

        $attrList = [
            'domain' => 'Домен личного кабинета',
            'portal_name' => 'Имя личного кабинета',
        ];
        echo "Введите параметры для настройки сервера (значение в скобках будет задано при пропуске параметра)\n";

        foreach ($attrList as $attr => $alias) {
            $buffer = (string)readline("  {$alias} ({$this->{$attr}}): ");
            $buffer = trim((string)$buffer);
            if (!empty($buffer)) {
                $this->{$attr} = $buffer;
            }
        };
        echo "\n";

        if (!isset($this->enable_1c_services)) {
            $this->enable_1c_services = $this->convertToBoolean(
                readline("Включить работу с SOAP-сервисами 1С? (Y / n): \n"),
                true
            );
        } else {
            $this->enable_1c_services = $this->convertToBoolean($this->enable_1c_services, true);
        }

        if (!isset($this->enable_1c_services)) {
            return false;
        }

        
        
        $path = FileHelper::normalizePath("{$this->rootPath}/frontend/web/install/src/controllers/EnvironmentController.php");
        require_once($path);
        $className = 'EnvironmentController';

        $environmentController = new $className();
        $environmentController->odinc_url = '';
        $environmentController->mail_host = '';
        $environmentController->mail_port = '';
        $environmentController->admin_mail = '';
        $environmentController->outer_mail = '';
        $environmentController->mail_protocol = '';
        $environmentController->mail_username = '';
        $environmentController->mail_password = '';
        $environmentController->odinc_url_stud = '';
        $environmentController->odinc_url_abit = '';
        $environmentController->odinc_login_stud = '';
        $environmentController->odinc_login_abit = '';
        $environmentController->odinc_password_stud = '';
        $environmentController->odinc_password_abit = '';

        $environmentController->domain = $this->domain;
        $environmentController->portal_name = $this->portal_name;
        $environmentController->enable_1c_services = $this->enable_1c_services ? 'true' : 'false';

        $state = [];
        if ($this->enable_1c_services) {
            $attrList = [
                'odinc_url' => 'Адрес публика 1С',
                'odinc_url_stud' => 'Префикс адреса публикации кабинета для студента',
                'odinc_url_abit' => 'Префикс адреса публикации кабинета для поступающего',
                'odinc_login_stud' => 'Логин для кабинета студента',
                'odinc_login_abit' => 'Логин для кабинета поступающего',
                'odinc_password_stud' => 'Пароль от кабинета студента',
                'odinc_password_abit' => 'Пароль от кабинета поступающего',
            ];
            echo "Введите параметры для настройки подключения к 1С серверу (значение в скобках будет задано при пропуске параметра)\n";

            foreach ($attrList as $attr => $alias) {
                $buffer = (string)readline("  {$alias} ({$this->{$attr}}): ");
                $buffer = trim((string)$buffer);
                if (!empty($buffer)) {
                    $this->{$attr} = $buffer;
                }
            };
            echo "\n";

            $environmentController->odinc_url = $this->odinc_url;
            $environmentController->odinc_url_stud = $this->odinc_url_stud;
            $environmentController->odinc_url_abit = $this->odinc_url_abit;
            $environmentController->odinc_login_stud = $this->odinc_login_stud;
            $environmentController->odinc_login_abit = $this->odinc_login_abit;
            $environmentController->odinc_password_stud = $this->odinc_password_stud;
            $environmentController->odinc_password_abit = $this->odinc_password_abit;

            $state = $environmentController->testAllConnection();
        }
        $messages = $environmentController->getErrorMessage($state, $this->enable_1c_services);
        if (!empty($messages)) {
            foreach ($messages as $message) {
                $this->printError("{$message}\n");
            }
            return false;
        }
        $environmentController->setEnv('/..', false);
        echo $this->ansiFormat(".env успешно сохранён\n\n", Console::FG_GREEN);

        return true;
    }

    


    private function installEnding()
    {
        $this->printNextStepNumber();

        
        
        $path = FileHelper::normalizePath("{$this->rootPath}/frontend/web/install/src/controllers/DictionaryController.php");
        require_once($path);
        $className = 'DictionaryController';
        $dictionaryController = new $className();

        echo 'Завершение установки. Копирование ' . $this->ansiFormat(".env\n", Console::FG_YELLOW);
        $finishResult = $dictionaryController->finish(false);
        if ($finishResult !== true) {
            $this->printError("{$finishResult}\n");
            return false;
        }

        echo $this->ansiFormat("Установка успешно завершена\n\n", Console::FG_GREEN);

        return true;
    }
}
