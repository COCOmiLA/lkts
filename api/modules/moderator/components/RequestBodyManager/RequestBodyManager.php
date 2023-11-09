<?php

namespace api\modules\moderator\components\RequestBodyManager;


use api\modules\moderator\components\LogManager\LogManager;
use yii\web\Request;

class RequestBodyManager
{
    


    private $request;

    


    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    


    public function getRequest(): Request
    {
        return $this->request;
    }

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getRequestBody()
    {
        $body = $this->getBodyFromRequest();

        LogManager::LogXML($body, "API[REQUEST]", "Полученные данные: \n\n");

        return $body;
    }

    protected function getBodyFromRequest()
    {
        return $this->getRequest()->getRawBody();
    }
}