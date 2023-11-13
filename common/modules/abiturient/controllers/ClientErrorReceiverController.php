<?php

namespace common\modules\abiturient\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class ClientErrorReceiverController extends Controller
{
    private const EVENT_INFO_TYPE = 'info';
    private const EVENT_WARNING_TYPE = 'warning';

    


    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $errorData = Yii::$app->request->post();
        $errorUrl = $errorData['url'] ?? '-';
        $errorType = $errorData['type'] ?? '-';
        $errorColno = $errorData['colno'] ?? '-';
        $errorLineno = $errorData['lineno'] ?? '-';
        $errorMessage = $errorData['message'] ?? '-';
        $errorFileName = $errorData['fileName'] ?? '-';
        $errorEventMessage = $errorData['errorMessage'] ?? '-';
        $controllerPath = ClientErrorReceiverController::extractControllerPath($errorUrl);

        if (!$errorMessage || $errorMessage == '-') {
            return;
        }

        $message = "
    Возникла ошибка на стороне клиента по причине: «{$errorMessage}»
    Полная ссылка на страницу: {$errorUrl}
    В файле: {$errorFileName} строка: {$errorLineno} колонка: {$errorColno}
        ";

        if ($errorEventMessage && $errorEventMessage != '-') {
            $message .= "\n\tТекст ошибки прерывания: «{$errorEventMessage}»";
        }

        switch ($errorType) {
            case ClientErrorReceiverController::EVENT_INFO_TYPE:
                Yii::info($message, "clientSide-{$controllerPath}");
                break;

            case ClientErrorReceiverController::EVENT_WARNING_TYPE:
                Yii::warning($message, "clientSide-{$controllerPath}");
                break;

            default:
                Yii::error($message, "clientSide-{$controllerPath}");
                break;
        }
    }

    




    private static function extractControllerPath(string $rawUrl = ''): string
    {
        return strtr(
            trim(
                parse_url(
                    $rawUrl,
                    PHP_URL_PATH
                ),
                '/'
            ),
            ['/' => '.']
        );
    }
}
