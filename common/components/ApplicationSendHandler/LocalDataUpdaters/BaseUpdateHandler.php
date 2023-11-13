<?php


namespace common\components\ApplicationSendHandler\LocalDataUpdaters;


use common\modules\abiturient\models\bachelor\BachelorApplication;

abstract class BaseUpdateHandler implements \common\components\ApplicationSendHandler\interfaces\IApplicationUpdateHandler
{

    


    private $application;

    public function __construct(BachelorApplication $application)
    {
        $this->setApplication($application);
    }

    


    public function setApplication(BachelorApplication $application): void
    {
        $this->application = $application;
    }

    


    public function getApplication(): BachelorApplication
    {
        return $this->application;
    }

    public function update(): bool
    {
        return true;
    }
}