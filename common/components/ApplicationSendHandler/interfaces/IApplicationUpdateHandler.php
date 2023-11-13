<?php


namespace common\components\ApplicationSendHandler\interfaces;


use common\modules\abiturient\models\bachelor\BachelorApplication;

interface IApplicationUpdateHandler
{
    public function getApplication(): BachelorApplication;

    public function setApplication(BachelorApplication $application);

    public function update(): bool;
}