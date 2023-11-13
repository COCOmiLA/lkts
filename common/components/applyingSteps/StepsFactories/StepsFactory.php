<?php

namespace common\components\applyingSteps\StepsFactories;


use common\components\applyingSteps\ApplicationApplyingStep;
use common\components\applyingSteps\ApplicationApplyingStepFactory;

class StepsFactory extends BaseStepsFactory
{
    public function getSteps(\common\modules\abiturient\models\bachelor\BachelorApplication &$application): array
    {
        return [
            ApplicationApplyingStepFactory::createStep($application, ApplicationApplyingStep::STEP_QEUSTIONARY),
            ApplicationApplyingStepFactory::createStep($application, ApplicationApplyingStep::STEP_EXAM_RESULT),
            ApplicationApplyingStepFactory::createStep($application, ApplicationApplyingStep::STEP_ADMISSION),
            ApplicationApplyingStepFactory::createStep($application, ApplicationApplyingStep::STEP_APPLICATION),
            ApplicationApplyingStepFactory::createStep($application, ApplicationApplyingStep::STEP_SCANS),
        ];
    }
}