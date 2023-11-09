<?php

namespace common\components\applyingSteps\StepsFactories;

use common\components\applyingSteps\ApplicationApplyingStep;
use common\components\applyingSteps\ApplicationApplyingStepFactory;

class FullPackageStepFactory extends BaseStepsFactory
{
    public function getSteps(\common\modules\abiturient\models\bachelor\BachelorApplication &$application): array
    {
        return [
            ApplicationApplyingStepFactory::createStep($application, ApplicationApplyingStep::STEP_FULL_PACKAGE),
        ];
    }
}