<?php

namespace common\modules\abiturient\validators\extenders\PersonalData;

use common\modules\abiturient\models\questionary\QuestionarySettings;
use common\modules\abiturient\validators\extenders\BaseValidationExtender;
use yii\helpers\ArrayHelper;

class ParentPersonalDataValidation extends BaseValidationExtender
{
    public function getRules(): array
    {
        $result = [];
        
        $required_birthplace = QuestionarySettings::getSettingByName('require_birth_place_parent');
        if ($required_birthplace) {
            $result = ArrayHelper::merge($result, [
                [['birth_place'], 'required']
            ]);
        }
        
        $required_citizenship = QuestionarySettings::getSettingByName('require_ctitizenship_parent');
        if ($required_citizenship) {
            $result = ArrayHelper::merge($result, [
                [['country_id'], 'required']
            ]);
        }
        
        return $result;
    }
}
