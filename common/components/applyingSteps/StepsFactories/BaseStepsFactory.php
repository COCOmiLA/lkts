<?php

namespace common\components\applyingSteps\StepsFactories;


abstract class BaseStepsFactory
{
    public function getSteps(\common\modules\abiturient\models\bachelor\BachelorApplication &$application): array
    {
        throw new \yii\base\UserException('Needs implementation');
    }
}