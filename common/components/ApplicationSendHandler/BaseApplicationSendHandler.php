<?php

namespace common\components\ApplicationSendHandler;

use common\components\ApplicationSendHandler\interfaces\IApplicationSendHandler;
use common\modules\abiturient\models\bachelor\BachelorApplication;





class BaseApplicationSendHandler implements IApplicationSendHandler
{
    const APP_SEND_TYPE_FULL = 'full';
    const APP_SEND_TYPE_BY_STEPS = 'steps';
    


    private $application;

    public function __construct(BachelorApplication $application)
    {
        $this->application = $application;
    }

    


    public function setApplication(BachelorApplication $application): void
    {
        $this->application = $application;
    }

    


    public function getApplication(): BachelorApplication
    {
        return $this->application;
    }

    public function send(): bool
    {
        return $this->getApplication()->sendAllApplicationTo1C();
    }

    public function updateDataAfterSuccessSending()
    {
        return;
    }
}