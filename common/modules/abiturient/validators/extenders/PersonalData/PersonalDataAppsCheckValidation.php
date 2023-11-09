<?php

namespace common\modules\abiturient\validators\extenders\PersonalData;

use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use common\modules\abiturient\validators\extenders\BaseValidationExtender;
use Yii;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class PersonalDataAppsCheckValidation extends BaseValidationExtender
{
    public $additional_application_types = [];

    public function getRules(): array
    {
        $result = [];
        $isCitizenshipRequired = QuestionarySettings::getSettingByName('require_ctitizenship_abiturient')
            || $this->isCitizenshipRequired($this->additional_application_types);
        $isSnilsRequired = $this->isSnilsRequired($this->additional_application_types);

        if ($isCitizenshipRequired) {
            $result = ArrayHelper::merge($result, [
                [['country_id'], 'required'],
            ]);
        }
        if ($isSnilsRequired) {
            $result = ArrayHelper::merge($result, [
                [['snils'], 'required'],
            ]);
        }
        $requiredBirthPlace = QuestionarySettings::getSettingByName('require_birth_place_abiturient');
        if ($requiredBirthPlace) {
            $result = ArrayHelper::merge($result, [
                [['birth_place'], 'required']
            ]);

        }

        return $result;
    }

    protected function isCitizenshipRequired(array $additional_application_types): bool
    {
        $user = ArrayHelper::getValue($this->model, 'abiturientQuestionary.user');
        $app_types_to_check = $this->getTypesToCheck($user, $additional_application_types);

        return boolval(array_filter($app_types_to_check, function (ApplicationType $app_type) {
            return ArrayHelper::getValue($app_type, 'citizenship_is_required');
        }));
    }

    protected function getTypesToCheck(?User $user, array $additional_application_types): array
    {
        $app_types_to_check = [];

        if ($user) {
            $app_types_to_check = ApplicationType::find()
                ->with(['campaign'])
                ->where(
                    [ApplicationType::tableName() . '.id' => $user->getApplications()->select(ApplicationType::tableName() . '.id')]
                )
                ->all();
        }

        $app_types_to_check = ArrayHelper::merge($app_types_to_check, $additional_application_types);
        return array_values(ArrayHelper::index($app_types_to_check, 'id'));
    }

    




    protected function isSnilsRequired(array $additional_application_types = []): bool
    {
        
        $citizenship = $this->model->citizenship;
        $user = ArrayHelper::getValue($this->model, 'abiturientQuestionary.user');
        if (!$citizenship || $citizenship->ref_key != Yii::$app->configurationManager->getCode('russia_guid')) {
            return false;
        }
        $app_types_to_check = $this->getTypesToCheck($user, $additional_application_types);

        return boolval(array_filter($app_types_to_check, function (ApplicationType $app_type) {
            return ArrayHelper::getValue($app_type, 'campaign.snils_is_required');
        }));
    }
}