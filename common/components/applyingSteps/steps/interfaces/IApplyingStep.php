<?php
namespace common\components\applyingSteps\steps\interfaces;

use common\modules\abiturient\models\bachelor\BachelorApplication;

interface IApplyingStep
{
    



    public function execute(): bool;

    



    public function makeStep() : bool;

    


    public function onSuccess() : void;

    


    public function onFail() : void;

    



    public function getStatusMessage() : string;

    



    public function setApplication(BachelorApplication $application) : void;
}