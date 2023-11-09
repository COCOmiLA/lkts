<?php

require_once(__DIR__ . '/../components/PermissionsCheckTrait.php');
require_once(__DIR__ . '/../../../../../vendor/autoload.php');

use backend\exceptions\DictionaryNoDataWarningHttpException;
use backend\models\DictionaryUpdateHistory;
use common\components\AppUpdate;
use common\components\ini\iniSet;
use yii\helpers\FileHelper;
use yii\web\BadRequestHttpException;


class DictionaryController
{
    use PermissionsCheckTrait;

    private function attachYii()
    {
        if (PHP_OS == 'WINNT' || PHP_OS == 'WIN32' || PHP_OS == 'Windows') {
            $root_folder = str_replace('frontend\web', '', getcwd());

            $path_pre = getcwd() . '\install\src\steps\pre.php';
        } else {
            $root_folder = str_replace('frontend/web', '', getcwd());

            $path_pre = getcwd() . '/install/src/steps/pre.php';
        }
        $conf_folder = 'confs';

        $path = $root_folder . $conf_folder;

        if (!is_writable($path)) {
            http_response_code(400);
            header('Content-Type: text/plain');
            echo "Ошибка: невозможно записать информацию в " . $path;
            die();
        }

        require_once($path_pre);
    }

    public function getDictsList()
    {
        $this->attachYii();
        http_response_code(200);
        header('Content-Type: application/json');

        echo json_encode(AppUpdate::DICTIONARY_UPDATE);
    }

    public function updateOneDictionary()
    {
        $this->attachYii();
        header('Content-Type: application/json');

        $method = Yii::$app->request->get('method');
        if (empty($method)) {
            throw new BadRequestHttpException('No method');
        }

        iniSet::disableTimeLimit();
        iniSet::extendMemoryLimit();

        $dictionaryManager = Yii::$app->dictionaryManager;
        if (method_exists($dictionaryManager, $method)) {
            try {
                Yii::$app->configurationManager->suspendUnspecifiedCodesError(true);
                [$status, $error] = call_user_func(array($dictionaryManager, $method));
            } catch (\Throwable $e) {
                $status = -1;
                $error = $e;
            }
            $error_message = '';
            if ($status === 1) { 
                DictionaryUpdateHistory::setUpdateTime($method, time());
                http_response_code(200);
                echo json_encode([
                    'status' => true,
                    'error_message' => $error_message
                ]);
                return;
            } else {
                if ($error) {
                    if ($error instanceof \Throwable) {
                        $error_message = "{$error->getMessage()}\n\n{$error->getTraceAsString()}";
                    } else {
                        $error_message = print_r($error, true);
                    }
                    Yii::error($error_message, 'INITIAL_DICTIONARY_UPDATE');
                    http_response_code(200);
                    echo json_encode([
                        'status' => false,
                        'error_message' => $error_message
                    ]);
                    return;
                }
                
                http_response_code(400);
                throw new DictionaryNoDataWarningHttpException();
            }
        }
        http_response_code(500);

        throw new BadRequestHttpException('Невозможно найти метод: ' . $method);
    }

    private function getPaths($root)
    {
        $root = FileHelper::normalizePath($root);
        if ($root[-1] != DIRECTORY_SEPARATOR) {
            $root = $root . DIRECTORY_SEPARATOR;
        }
        $conf = 'confs' . DIRECTORY_SEPARATOR;
        $path_pre = $root . 'frontend/web/install/src/steps/pre.php';

        return [
            'conf' => $conf,
            'path_pre' => FileHelper::normalizePath($path_pre),
            'destination' => $root,
        ];
    }

    public function finish($forGuiInstaller = true)
    {
        $root = __DIR__ . '/../../../../../';

        [
            'conf' => $conf,
            'destination' => $destination
        ] = $this->getPaths($root);

        $path = "{$destination}.env";

        try {
            $this->ensureIsReadable($path);
            $this->ensureIsWritable($path);
        } catch (\Throwable $e) {
            if (!$forGuiInstaller) {
                return $e->getMessage();
            }

            http_response_code(400);
            header('Content-Type: text/plain');
            echo $e->getMessage();
            die();
        }

        if (!$forGuiInstaller) {
            return true;
        }

        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(E_ERROR);
        header('Location: /');
    }
}
