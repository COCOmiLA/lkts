<?php

namespace common\modules\abiturient\validators\extenders;

use yii\base\BaseObject;

class BaseValidationExtender extends BaseObject
{
    public $model;

    public function getRules(): array
    {
        return [];
    }

    public function modelPreparationCallback()
    {

    }
}