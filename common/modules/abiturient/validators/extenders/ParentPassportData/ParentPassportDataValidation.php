<?php

namespace common\modules\abiturient\validators\extenders\ParentPassportData;

use common\models\settings\ParentDataSetting;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\validators\extenders\BaseValidationExtender;

class ParentPassportDataValidation extends BaseValidationExtender
{
    public function modelPreparationCallback()
    {
        $setting = ParentDataSetting::findOne(['name' => 'require_parent_passport_data']);
        if ($setting && !$setting->value) {
            $this->model->setScenario(PassportData::SCENARIO_NOT_REQUIRED);
        }
    }
}
