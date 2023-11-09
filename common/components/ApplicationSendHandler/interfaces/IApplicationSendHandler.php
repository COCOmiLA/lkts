<?php

namespace common\components\ApplicationSendHandler\interfaces;


use common\modules\abiturient\models\bachelor\BachelorApplication;

interface IApplicationSendHandler
{
    public function getApplication(): BachelorApplication;

    public function setApplication(BachelorApplication $application);

    public function send(): bool;

    public function updateDataAfterSuccessSending();
}