<?php

require_once(__DIR__ . '/../../../../../vendor/autoload.php');

use yii\helpers\FileHelper;

class EnvironmentController
{
    public $domain;
    public $portal_name;
    public $admin_mail;
    public $outer_mail;
    public $odinc_url;

    public $odinc_url_stud;
    public $odinc_login_stud;
    public $odinc_password_stud;

    public $odinc_url_abit;
    public $odinc_login_abit;
    public $odinc_password_abit;

    private $odinc_url_web_app;
    private $odinc_login_web_app;
    private $odinc_password_web_app;

    public $mail_host;
    public $mail_port;
    public $mail_protocol;
    public $mail_username;
    public $mail_password;

    public $enable_1c_services;

    const REST_URI = '/hs/';
    const ABIT_SOAP_URI = '/ws/webabit.1cws?wsdl';
    const STUDENT_SOAP_URI = '/ws/Study.1cws?wsdl';
    const ENV_PATH = '/.env';

    const PASSWORD_HASH = '7c4a8d09ca3762af61e59520943dc26494f8941b';

    private $isUnauth = false;

    public function setup()
    {
        $this->domain = $_POST['WebAddress'];
        $this->portal_name = $_POST['WebName'];

        $this->admin_mail = $_POST['WebAdminEmail'];
        $this->outer_mail = $_POST['WebOutEmail'];
        $this->mail_protocol = $_POST['MailProtocol'];
        if (empty($this->mail_protocol)) { 
            $this->mail_protocol = 'tls';
        } elseif ($this->mail_protocol === 'unsafe') { 
            $this->mail_protocol = '';
        }
        $this->mail_host = $_POST['MailHost'];
        $this->mail_port = $_POST['MailPort'];
        $this->mail_username = $_POST['MailUsername'];
        $this->mail_password = $_POST['MailPassword'];

        $this->odinc_login_stud = $_POST['OdinNameStud'];
        $this->odinc_password_stud = $_POST['OdinPasswordStud'];
        $this->odinc_url_stud = $_POST['OdinWebStud'];
        $this->odinc_login_abit = $_POST['OdinNameAbit'];
        $this->odinc_password_abit = $_POST['OdinPasswordAbit'];
        $this->odinc_url_abit = $_POST['OdinWebAbit'];
        $this->odinc_url = rtrim((string)$_POST['OdinWeb'], '/');

        $this->odinc_login_web_app = $_POST['OdinNameWebApp'];
        $this->odinc_password_web_app = $_POST['OdinPasswordWebApp'];
        $this->odinc_url_web_app = $_POST['OdinWebWebApp'];

        $state = $this->testAllConnection();

        $haveErrors = false;
        $messages = $this->getErrorMessage($state);

        if (!empty($messages)) {
            $haveErrors = true;
        } else {
            $this->setEnv('/../..');
        }

        $messages = implode('<br>', $messages);

        if ($haveErrors) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo $messages;
            die();
        } else {
            http_response_code(200);
            header('Content-Type: text/plain');
            echo true;
        }
    }

    public function getErrorMessage($state = [], $enable1cServices = true)
    {
        $messages = [];
        if ($this->isUnauth) {
            $messages[] = 'Ошибка авторизации, проверьте правильность имени пользователя и пароля 1С';
        }

        if (empty($this->domain)) {
            $messages[] = 'Необходимо указать адрес портала';
        }
        if (empty($this->portal_name)) {
            $messages[] = 'Необходимо указать наименование портала';
        }

        if ($enable1cServices) {
            if (empty($this->odinc_url_abit)) {
                $messages[] = "SOAP-сервисы приёмной кампании 1С недоступны. Сервисы ожидаются по адресу: {$this->odinc_url_abit}";
            } elseif (strpos($this->odinc_url_abit, '://') !== false) {
                $messages[] = 'Необходимо указать относительный адрес 1C для веб-сервис "ЛК Студента"';
            }
            if (empty($this->odinc_url_stud)) {
                $messages[] = "Студенческие SOAP-сервисы 1С недоступны. Сервисы ожидаются по адресу: {$this->odinc_url_stud}";
            } elseif (strpos($this->odinc_url_stud, '://') !== false) {
                $messages[] = 'Необходимо указать относительный адрес 1C для веб-сервис "ЛК Поступающего"';
            }
            if (empty($this->odinc_url_web_app)) {
                $messages[] = "WebApp SOAP-сервисы 1С недоступны. Сервисы ожидаются по адресу: {$this->odinc_url_web_app}";
            } elseif (strpos($this->odinc_url_web_app, '://') !== false) {
                $messages[] = 'Необходимо указать относительный адрес 1C для веб-сервиса "WebApplication"';
            }
            if (empty($this->odinc_login_stud)) {
                $messages[] = 'Необходимо указать имя пользователя 1C для веб-сервис "ЛК Студента"';
            }
            if (empty($this->odinc_login_abit)) {
                $messages[] = 'Необходимо указать имя пользователя 1C для веб-сервис "ЛК Поступающего"';
            }
            if (empty($this->odinc_login_web_app)) {
                $messages[] = 'Необходимо указать имя пользователя 1C для веб-сервис "WebApplication"';
            }
            if (empty($this->odinc_url)) {
                $messages[] = 'Необходимо указать адрес публикации базы 1С';
            } else {
                foreach ($state as $key => $st) {
                    if (!$st) {
                        switch ($key) {
                            case ('abit_soap'):
                                $messages[] = "SOAP-сервисы приёмной кампании 1С недоступны. Сервисы ожидаются по адресу: {$this->odinc_url_abit}";
                                break;
                            case ('student_soap'):
                                $messages[] = "Студенческие SOAP-сервисы 1С недоступны. Сервисы ожидаются по адресу: {$this->odinc_url_stud}";
                                break;
                            case ('web_app_soap'):
                                $messages[] = "Web Application SOAP-сервисы 1С недоступны. Сервисы ожидаются по адресу: {$this->odinc_url_web_app}";
                                break;
                        }
                    }
                }
            }
        }
        return $messages;
    }

    public function testAllConnection()
    {
        $state = [];

        if ($this->testConnection($this->odinc_url_abit, 'abit') && $this->loadTestData($this->odinc_url_abit, [
                'login' => $this->odinc_login_abit,
                'password' => $this->odinc_password_abit,
            ], 'TestConnect')) {
            $state['abit_soap'] = true;
        } else {
            $state['abit_soap'] = false;
        }

        if ($this->testConnection($this->odinc_url_stud, 'stud') && $this->loadTestData($this->odinc_url_stud, [
                'login' => $this->odinc_login_stud,
                'password' => $this->odinc_password_stud,
            ], 'Authorization', ['UserId' => '', 'Login' => 'Test', 'PasswordHash' => self::PASSWORD_HASH])) {
            $state['student_soap'] = true;
        } else {
            $state['student_soap'] = false;
        }

        if ($this->testConnection($this->odinc_url_web_app, 'web_app') && $this->loadTestData($this->odinc_url_web_app,
                [
                    'login' => $this->odinc_login_web_app,
                    'password' => $this->odinc_password_web_app,
                ],
                'TestConnection')) {
            $state['web_app_soap'] = true;
        } else {
            $state['web_app_soap'] = false;
        }

        return $state;
    }

    protected function testConnection($uri, $who = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->odinc_url . $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        if ($who == 'abit') {
            curl_setopt($ch, CURLOPT_USERPWD, "$this->odinc_login_abit:$this->odinc_password_abit");
        } elseif ($who == 'web_app') {
            curl_setopt($ch, CURLOPT_USERPWD, "$this->odinc_login_web_app:$this->odinc_password_web_app");
        } else {
            
            curl_setopt($ch, CURLOPT_USERPWD, "$this->odinc_login_stud:$this->odinc_password_stud");
        }
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpcode !== 200) {
            if ($httpcode == 401) {
                $this->isUnauth = true;
            }
            return false;
        }

        return true;
    }

    private function loadTestData($uri, $auth_options, $action, $params = null)
    {
        try {
            $client = new SoapClient(
                $this->odinc_url . $uri,
                [
                    'login' => $auth_options['login'],
                    'password' => $auth_options['password'],
                    'trace' => false,
                    'exceptions' => true,
                    'cache_wsdl' => WSDL_CACHE_NONE
                ]
            );

            if ($client != null) {
                $result = $client->$action($params);
                return true;
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    





    public function setEnv($pathPrefix, $forGuiInstaller = true)
    {
        $project_root = getcwd() . $pathPrefix;
        $path = FileHelper::normalizePath($project_root . self::ENV_PATH);

        if (!is_writable($path)) {
            if (!$forGuiInstaller) {
                return 'невозможно записать информацию в ' . self::ENV_PATH;
            }

            http_response_code(400);
            header('Content-Type: text/plain');
            echo 'Ошибка: невозможно записать информацию в ' . self::ENV_PATH;
            die();
        }

        if (empty($this->enable_1c_services)) {
            $this->enable_1c_services = 'true';
        }

        $this->updateEnv(
            'FRONTEND_URL',
            "FRONTEND_URL=\"{$this->domain}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'PORTAL_NAME',
            "PORTAL_NAME=\"{$this->portal_name}\"" . PHP_EOL,
            $path
        );

        $this->updateEnv(
            'ADMIN_EMAIL',
            "ADMIN_EMAIL=\"{$this->admin_mail}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'FROM_EMAIL',
            "FROM_EMAIL=\"{$this->outer_mail}\"" . PHP_EOL,
            $path
        );


        $this->updateEnv(
            'SERVICE_URI',
            "SERVICE_URI=\"{$this->odinc_url}" . self::REST_URI . '"' . PHP_EOL,
            $path
        );

        $this->updateEnv(
            'STUDENT_WSDL',
            "STUDENT_WSDL=\"{$this->odinc_url}{$this->odinc_url_stud}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'STUDENT_LOGIN',
            "STUDENT_LOGIN=\"{$this->odinc_login_stud}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'STUDENT_PASSWORD',
            "STUDENT_PASSWORD=\"{$this->odinc_password_stud}\"" . PHP_EOL,
            $path
        );

        $this->updateEnv(
            'ABIT_WSDL',
            "ABIT_WSDL=\"{$this->odinc_url}{$this->odinc_url_abit}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'ABIT_LOGIN',
            "ABIT_LOGIN=\"{$this->odinc_login_abit}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'ABIT_PASSWORD',
            "ABIT_PASSWORD=\"{$this->odinc_password_abit}\"" . PHP_EOL,
            $path
        );

        $this->updateEnv(
            'WEB_APP_WSDL',
            "WEB_APP_WSDL=\"{$this->odinc_url}{$this->odinc_url_web_app}\"" . PHP_EOL,
            $path);

        $this->updateEnv(
            'WEB_APP_LOGIN',
            "WEB_APP_LOGIN=\"{$this->odinc_login_web_app}\"" . PHP_EOL,
            $path);

        $this->updateEnv(
            'WEB_APP_PASSWORD',
            "WEB_APP_PASSWORD=\"{$this->odinc_password_web_app}\"" . PHP_EOL,
            $path);


        $this->updateEnv(
            'MAIL_HOST',
            "MAIL_HOST=\"{$this->mail_host}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'MAIL_PORT',
            "MAIL_PORT=\"{$this->mail_port}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'MAIL_PROTOCOL',
            "MAIL_PROTOCOL=\"{$this->mail_protocol}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'MAIL_USERNAME',
            "MAIL_USERNAME=\"{$this->mail_username}\"" . PHP_EOL,
            $path
        );
        $this->updateEnv(
            'MAIL_PASSWORD',
            "MAIL_PASSWORD=\"{$this->mail_password}\"" . PHP_EOL,
            $path
        );

        $this->updateEnv(
            'ENABLE_1C_SERVICES',
            "ENABLE_1C_SERVICES={$this->enable_1c_services}" . PHP_EOL,
            $path
        );

        return null;
    }

    private function updateEnv($sub_str, $content, $path)
    {
        $lines = file($path);

        $new_lines = [];
        foreach ($lines as $string) {
            if (strpos($string, $sub_str) !== false) {
                $new_lines[] = $content;
            } else {
                $new_lines[] = $string;
            }
        }

        file_put_contents($path, $new_lines);
    }
}
