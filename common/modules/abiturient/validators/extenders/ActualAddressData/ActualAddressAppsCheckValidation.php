<?php

namespace common\modules\abiturient\validators\extenders\ActualAddressData;

use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\bachelor\ApplicationType;
use yii\helpers\ArrayHelper;

class ActualAddressAppsCheckValidation extends \common\modules\abiturient\validators\extenders\BaseValidationExtender
{
    public $additional_application_types = [];

    public function modelPreparationCallback()
    {
        if (!$this->isRequiredToFill($this->additional_application_types)) {
            $this->model->setScenario(AddressData::SCENARIO_NOT_REQUIRED);
        }
    }

    



    protected function isRequiredToFill(array $additional_application_types = []): bool
    {
        
        $user = ArrayHelper::getValue($this->model, 'abiturientQuestionary.user');
        $app_types_to_check = [];
        if ($user) {
            $app_types_to_check = ApplicationType::find()->where(
                [ApplicationType::tableName() . '.id' => $user->getApplications()->select(ApplicationType::tableName() . '.id')]
            )->all();
        }
        $app_types_to_check = ArrayHelper::merge($app_types_to_check, $additional_application_types);

        $app_types_to_check = array_values(ArrayHelper::index($app_types_to_check, 'id'));

        return !!array_filter($app_types_to_check, function (ApplicationType $type) {
            return $type->needToValidateActualAddress();
        });
    }
}