<?php
ini_set("display_errors", "off");
require_once(__DIR__ . '/../components/ErrorHandler.php');
register_shutdown_function([new ErrorHandler(), 'handle']);

require_once(__DIR__ . '/../components/PermissionsCheckTrait.php');
require_once(__DIR__ . '/../../../../../vendor/autoload.php');

use yii\helpers\FileHelper;

class WebserverController
{
    use PermissionsCheckTrait;

    public function setup()
    {
        $webserver = $_POST['group1'];
        $application_root = FileHelper::normalizePath(__DIR__ . '/../../../../..') . DIRECTORY_SEPARATOR;

        $config_root = "{$application_root}confs" . DIRECTORY_SEPARATOR; 
        $result = false;
        $errorMessages = [];
        $httpStatus = null;
        $url = "";

        try {
            switch ($webserver) {
                case('apache'):
                    $configFile = "{$config_root}.htaccess";
                    $this->ensureIsReadable($configFile);
                    $this->ensureIsWritable($application_root);
                    copy($configFile, "{$application_root}.htaccess");

                    if (file_exists("{$application_root}.htaccess")) { 
                        $result = true;
                        $url = "{$_SERVER['HTTP_ORIGIN']}/user/sign-in/login";
                    }
                    break;

                case('iis'):
                    $configFile = "{$config_root}web.config";
                    $this->ensureIsReadable($configFile);
                    $this->ensureIsWritable($application_root);
                    copy($configFile, "{$application_root}web.config");

                    if (file_exists("{$application_root}web.config")) {
                        $result = true;
                        if ($_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1') {
                            $url = "http://{$_SERVER['HTTP_HOST']}/user/sign-in/login";
                        } else {
                            $url = "https://{$_SERVER['HTTP_HOST']}/user/sign-in/login";
                        }
                    }
                    break;

                default: 
                    $result = true;
            }
        } catch (Throwable $ex) {
            $errorMessages[] = $ex->getMessage();
            error_log($ex);
            http_response_code(400);
            header('Content-Type: text/plain');
            echo false;
        }

        $response = true;
        if ($webserver !== 'other') {
            $response = @get_headers($url);
            $httpStatus = $this->getHttpStatus($url);
        }

        if (!$response || $response[0] == "{$_SERVER['SERVER_PROTOCOL']} 404 Not Found" || ($httpStatus >= 400 && $httpStatus < 500) || $httpStatus === 0) {
            $response = false;
            $errorMessages[] = "URL портала не доступен";
        } elseif ($httpStatus >= 500) {
            $response = false;
            $errorMessages[] = "При работе портала произошла ошибка";
        } else {
            $response = true;
        }

        if ($result && $response !== false) { 
            http_response_code(200);
            header('Content-Type: text/plain');
            echo true;
        } else { 
            if ($response === false) {
                if ($webserver == 'apache') {
                    http_response_code(401);
                } elseif ($webserver == 'iis') {
                    http_response_code(402);
                } else {
                    http_response_code(400);
                }
            }

            if ($result === false || $httpStatus >= 500) {
                http_response_code(400);
            }
            header('Content-Type: text/plain');
            echo implode("<br>", $errorMessages);
        }
    }

    protected function getHttpStatus($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return $httpCode;
    }
}
