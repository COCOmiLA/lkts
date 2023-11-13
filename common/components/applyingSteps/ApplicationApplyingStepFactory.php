<?php

namespace common\components\applyingSteps;

use common\components\applyingSteps\steps\FullPackageStep;
use common\components\applyingSteps\steps\interfaces\IApplyingStep;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use yii\base\UserException;





class ApplicationApplyingStepFactory
{
    public static function createStep(BachelorApplication $application, $name)
    {
        $step = null;
        switch ($name) {
            case ApplicationApplyingStep::STEP_FULL_PACKAGE:
                $step = new FullPackageStep();
                break;
            default:
                throw new UserException('Unknown step name');
        }
        $step->application = $application;
        return $step;
    }
}