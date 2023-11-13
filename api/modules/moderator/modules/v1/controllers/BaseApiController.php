<?php


namespace api\modules\moderator\modules\v1\controllers;


use api\modules\moderator\components\LogManager\LogManager;
use api\modules\moderator\components\RequestBodyManager\RequestBodyManager;
use yii\rest\ActiveController;

class BaseApiController extends ActiveController
{
    


    private $requestBodyManager;

    


    public function getRequestBodyManager(): RequestBodyManager
    {
        return $this->requestBodyManager;
    }

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->requestBodyManager = new RequestBodyManager(\Yii::$app->request);
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        LogManager::LogXML($result, "API[RESPONSE]", "Отправленные данные: \n\n");
        return $result;
    }
}