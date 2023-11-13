<?php

namespace api\modules\moderator\modules\v1\models\EntrantApplication;


use api\modules\moderator\modules\v1\models\EntrantQuestionnaire\EntrantQuestionnaire;
use common\components\applyingSteps\StepsFactories\EmptyStepsFactory;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerValidationException;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerWrongClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\repository\ChangeHistoryRepository;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use yii\db\ActiveQuery;
use yii\web\ForbiddenHttpException;






class EntrantApplication extends BachelorApplication
{
    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->initApplyingSteps(new EmptyStepsFactory());
    }

    public function getLastChangeHistory()
    {
        $app_changes = ChangeHistoryRepository::getChangeHistoryIds($this, false);
        return ChangeHistory::find()
            ->where(['change_history.id' => $app_changes])
            ->orderBy(['change_history.created_at' => SORT_DESC])
            ->one();
    }

    public function getLastChangeHistoryDate(): string
    {
        $lastChangeHistory = $this->lastChangeHistory;
        if (is_null($lastChangeHistory)) {
            return '';
        }

        return date("Y-m-d H:i:s", $lastChangeHistory->created_at);
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $user = $this->user;
        
        $questionnaire = EntrantQuestionnaire::find()
            ->where(['id' => $this->getAbiturientQuestionary()
                ->select('id')])
            ->one();

        return [
            'EntrantRef' => UserReferenceTypeManager::GetProcessedUserReferenceType($user),
            'EntrantPortalGUID' => $this->user->system_uuid,
            'Name' => $questionnaire->personalData->firstname,
            'Surname' => $questionnaire->personalData->lastname,
            'Patronymic' => $questionnaire->personalData->middlename,
            'NameEng' => '',
            'SurnameEng' => '',
            'SNILS' => $questionnaire->personalData->snils,
            'GenderRef' => ReferenceTypeManager::GetReference($questionnaire->personalData, 'relGender'),
            'Birthday' => $questionnaire->personalData->birthdate,
            'CitizenshipRef' => ReferenceTypeManager::GetReference($questionnaire->personalData, 'citizenship'),
            'Email' => $user->email,
            'EntrantUniqueCode' => $questionnaire->personalData->entrant_unique_code,
            'PhoneMobile' => $questionnaire->personalData->main_phone,
            'PhoneHome' => $questionnaire->personalData->secondary_phone,
            'IdentificateDocuments' => $questionnaire->passportData,
            'UpdateDate' => $this->getLastChangeHistoryDate(),
            'ApplicationState' => $this->status == ApplicationInterface::STATUS_WANTS_TO_RETURN_ALL ? 'DocumentsReturn' : 'DocumentsApply',
        ];
    }

    



    public function getSpecialities()
    {
        return $this->hasMany(EntrantSpeciality::class, ['application_id' => 'id'])
            ->active()
            ->with(['speciality'])
            ->joinWith(['specialityPriority speciality_priority'])
            ->orderBy(["speciality_priority.enrollment_priority" => SORT_ASC, 'speciality_priority.inner_priority' => SORT_ASC]);
    }

    



    public function getEducation()
    {
        return $this->hasOne(EntrantEducationDocument::class, ['application_id' => 'id'])->active();
    }

    






    public function checkApplicationBlocked(): bool
    {
        [$result, $_] = $this->isApplicationBlocked();
        if ($result) {
            throw new ForbiddenHttpException("Данное заявление заблокировано другим модератором ({$this->getBlockerName()})");
        }
        return $result;
    }
}