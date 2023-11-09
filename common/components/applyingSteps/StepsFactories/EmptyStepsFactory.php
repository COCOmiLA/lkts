<?php

namespace common\components\applyingSteps\StepsFactories;


use common\modules\abiturient\models\bachelor\BachelorApplication;

class EmptyStepsFactory extends BaseStepsFactory
{
    public function getSteps(BachelorApplication &$application): array
    {
        return [];
    }
}