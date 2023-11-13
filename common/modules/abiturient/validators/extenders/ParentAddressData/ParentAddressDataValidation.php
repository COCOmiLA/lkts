<?php

namespace common\modules\abiturient\validators\extenders\ParentAddressData;

use common\models\settings\ParentDataSetting;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\validators\extenders\BaseValidationExtender;

class ParentAddressDataValidation extends BaseValidationExtender
{
    public function modelPreparationCallback()
    {
        $setting = ParentDataSetting::findOne(['name' => 'require_parent_address_data']);
        if ($setting && !$setting->value) {
            $this->model->setScenario(AddressData::SCENARIO_NOT_REQUIRED);
        }
    }
}
