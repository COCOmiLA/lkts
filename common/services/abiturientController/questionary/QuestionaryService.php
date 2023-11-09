<?php

namespace common\services\abiturientController\questionary;

use common\components\AttachmentManager;
use common\components\PageRelationManager;
use common\components\RegulationManager;
use common\components\RegulationRelationManager;
use common\models\Attachment;
use common\models\EmptyCheck;
use common\models\repositories\UserRegulationRepository;
use common\models\User;
use common\models\UserRegulation;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\ActualAddressData;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\interfaces\IQuestionnaireValidateModelInterface;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\PersonalData;
use common\modules\abiturient\models\repositories\FileRepository;
use common\modules\abiturient\models\repositories\RegulationRepository;
use frontend\models\UpdateContactForm;
use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;



class QuestionaryService extends AbiturientQuestionaryService
{
    
    private $currentApplication = null;

    





    public function checkAccessibilityToRelatedBachelorApplication(User $currentUser, ?int $id = null): void
    {
        if (is_null($id)) {
            return;
        }

        BachelorApplication::checkAccessibility($currentUser, $id);
    }

    





    public function getRelatedBachelorApplication(User $currentUser, ?int $id = null): ?BachelorApplication
    {
        if (is_null($id)) {
            return null;
        }

        $this->currentApplication = BachelorApplication::findOne([
            'id' => $id,
            'user_id' => $currentUser->id,
        ]);

        return $this->currentApplication;
    }

    




    public function getQuestionary(User $currentUser): array
    {
        $needRefreshPage = false;
        $questionary = null;
        if ($this->currentApplication) {
            $questionary = $this->currentApplication->abiturientQuestionary;
        }

        if (!$questionary) {
            $questionary = $currentUser->abiturientQuestionary;
        }

        if (!$questionary) {
            $questionary = new AbiturientQuestionary();
            $questionary->user_id = $currentUser->id;
            $questionary->status = AbiturientQuestionary::STATUS_CREATED;
            $questionary->draft_status = IDraftable::DRAFT_STATUS_CREATED;
            $questionary->save(false);

            
            if ($currentUser && $currentUser->userRef) {
                $needRefreshPage = true;
                AbiturientQuestionary::UpdateDataFromOneS($questionary);
            }
        }

        return [
            'questionary' => $questionary,
            'needRefreshPage' => $needRefreshPage,
        ];
    }

    





    public function getRegulations(User $currentUser, AbiturientQuestionary $questionary): array
    {
        $regulations = [];

        $existing_regulation = UserRegulationRepository::GetUserRegulationsByQuestionaryAndRelatedEntity(
            $questionary,
            [RegulationRelationManager::RELATED_ENTITY_QUESTIONARY, RegulationRelationManager::RELATED_ENTITY_REGISTRATION]
        );
        $regulation_to_add = RegulationRepository::GetNotExistingRegulationsForEntity(
            [RegulationRelationManager::RELATED_ENTITY_QUESTIONARY, RegulationRelationManager::RELATED_ENTITY_REGISTRATION],
            ArrayHelper::getColumn($existing_regulation, 'regulation_id')
        );
        foreach ($regulation_to_add as $regulation) {
            $userRegulation = new UserRegulation();
            $userRegulation->regulation_id = $regulation->id;
            $userRegulation->owner_id = $currentUser->id;
            $userRegulation->abiturient_questionary_id = $questionary->id;
            $regulations[] = $userRegulation;
        }

        
        $regulations = array_merge($regulations, $existing_regulation);
        foreach ($regulations as $regulation) {
            if ($regulation->regulation->attachment_type && !$regulation->getAttachments()->exists()) {
                $regulationAttachment = new Attachment();
                $regulationAttachment->owner_id = $currentUser->id;
                $regulationAttachment->attachment_type_id = $regulation->regulation->attachment_type;
                $regulation->setRawAttachment($regulationAttachment);
            }
        }

        ArrayHelper::multisort($regulations, 'regulation_id', SORT_ASC, SORT_NUMERIC);

        return $regulations;
    }

    




    public function getAttachments(AbiturientQuestionary $questionary): array
    {
        return FileRepository::GetQuestionaryCollectionsFromTypes($questionary, [
            PageRelationManager::RELATED_ENTITY_REGISTRATION,
            PageRelationManager::RELATED_ENTITY_QUESTIONARY
        ]);
    }

    







    public function saveRegulationsOrAttachmentsWithChangesReturned(
        AbiturientQuestionary $questionary,
        array                 $regulations,
        array                 $attachments,
        bool                  $canEdit
    ): bool
    {
        $anyDataChanged = false;
        if ($canEdit && RegulationManager::handleRegulations($regulations, $this->request)) {
            $anyDataChanged = true;
        }

        if (
            ($canEdit || $questionary->hasPassedApplicationWithEditableAttachments()) &&
            AttachmentManager::handleAttachmentUpload($attachments, $regulations)
        ) {
            $anyDataChanged = true;
        }

        return $anyDataChanged;
    }

    








    public function processFromPost(
        User                  $currentUser,
        AbiturientQuestionary $questionary,
        PersonalData          $personalData,
        AddressData           $addressData,
        ActualAddressData     $actualAddressData
    ): array
    {
        $isSaved = false;
        $validated = false;
        $anyDataChanged = false;

        $personalData = $this->processBasicDataFromPost($currentUser, $personalData);
        $addressData = $this->processAddressDataFromPost($addressData, $questionary);
        $actualAddressData = $this->processAddressDataFromPost($actualAddressData, $questionary);

        if (
            $this->validateQuestionnaireModel($questionary) &&
            $this->validateQuestionnaireModel($addressData) &&
            $this->validateQuestionnaireModel($personalData) &&
            $this->validateQuestionnaireModel($actualAddressData)
        ) {
            $validated = true;
            $personalData->questionary_id = $questionary->id;
            [
                'isSaved' => $isSaved,
                'anyDataChanged' => $anyDataChanged,
            ] = $this->afterValidationProcess(
                $currentUser,
                $questionary,
                $personalData,
                $addressData,
                $actualAddressData
            );
        }

        return [
            'isSaved' => $isSaved,
            'validated' => $validated,
            'anyDataChanged' => $anyDataChanged,
        ];
    }

    




    public function updateContactFromPost(User $currentUser): void
    {
        $model = new UpdateContactForm();

        $model->user = $currentUser;
        if ($this->request->post('update_email')) {
            $model->email = $this->request->post('update_email');
        }
        if (!EmptyCheck::isEmpty($this->request->post('main_phone'))) {
            $model->main_phone = $this->request->post('main_phone');
        }
        if ($this->request->post('secondary_phone')) {
            $model->secondary_phone = $this->request->post('secondary_phone');
        }
        $model->save();

        $applications = $currentUser->getApplications()
            ->andWhere([BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_CREATED])
            ->all();
        foreach ($applications as $application) {
            

            $application->addApplicationHistory(ApplicationHistory::TYPE_QUESTIONARY_CHANGED);
            $application->resetStatus();
        }
    }

    






    private function validateQuestionnaireModel(ActiveRecord $model): bool
    {
        $name = get_class($model);
        if (!$model instanceof IQuestionnaireValidateModelInterface) {
            throw new UserException("При проверке модели ожидался класс исполняющий интерфейс «IQuestionnaireValidateModelInterface». Обрабатываемый класс: {$name}");
        }

        if (!$status = $model->validate()) {
            $errors = $model->errors;
            Yii::error(
                "Невозможно сохранить анкету пользователя.\n\nМодель: ($name)\n\nДанные:" .
                print_r($model->attributes, true) .
                "\n\nОшибки:\n\n" .
                print_r($errors, true),
                'QuestionaryService.validateQuestionnaireModel'
            );
        }

        return $status;
    }

    







    private function processAddressDataFromPost(AddressData $addressData, AbiturientQuestionary $questionary): AddressData
    {
        if (!$addressData->load($this->request->post())) {
            return $addressData;
        }

        if ($addressData->isNewRecord) {
            $addressData->questionary_id = $questionary->id;
        }

        if ($addressData->area_id == "null") {
            $addressData->area_id = null;
        }

        $addressData->cleanUnusedAttributes();
        if ($addressData->country != null && $addressData->country->ref_key != $this->configurationManager->getCode('russia_guid')) {
            $addressData->not_found = true;
        }
        $addressData->processKLADRCode();

        return $addressData;
    }

    




    private function hasChangedAttributesAndSave(ActiveRecord $questionnaireDependentModel): bool
    {
        if (!$questionnaireDependentModel instanceof IQuestionnaireValidateModelInterface) {
            $name = get_class($questionnaireDependentModel);
            throw new UserException("При сохранении модели ожидался класс исполняющий интерфейс «IQuestionnaireValidateModelInterface». Обрабатываемый класс: {$name}");
        }

        return $questionnaireDependentModel->hasChangedAttributes() && $questionnaireDependentModel->save(false);
    }

    






    private function clearJoinCache(AbiturientQuestionary $questionary): void
    {
        unset($questionary->addressData);
        unset($questionary->passportData);
        unset($questionary->personalData);
        unset($questionary->actualAddressData);
    }

    






    private function saveProcess(
        User                  $currentUser,
        AbiturientQuestionary $questionary,
        bool                  $anyDataChanged
    ): AbiturientQuestionary
    {
        if ($anyDataChanged) {
            $applications = $currentUser->getApplications()->andWhere([BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_CREATED])->all();
            foreach ($applications as $application) {
                $application->addApplicationHistory(ApplicationHistory::TYPE_QUESTIONARY_CHANGED);
            }
        }
        $questionary->status = AbiturientQuestionary::STATUS_SENT;
        $questionary->save(false);

        return $questionary;
    }

    








    private function afterValidationProcess(
        User                  $currentUser,
        AbiturientQuestionary $questionary,
        PersonalData          $personalData,
        AddressData           $addressData,
        ActualAddressData     $actualAddressData
    ): array
    {
        $isSaved = false;

        $anyDataChanged = false;
        if ($this->hasChangedAttributesAndSave($addressData)) {
            $anyDataChanged = true;
        }
        if ($this->hasChangedAttributesAndSave($personalData)) {
            $anyDataChanged = true;
        }
        if ($this->hasChangedAttributesAndSave($actualAddressData)) {
            $anyDataChanged = true;
        }

        $this->clearJoinCache($questionary);

        $this->saveProcess($currentUser, $questionary, $anyDataChanged);
        if ($anyDataChanged) {
            $isSaved = true;
        }


        return [
            'isSaved' => $isSaved,
            'anyDataChanged' => $anyDataChanged,
        ];
    }

    





    private function processBasicDataFromPost(User $currentUser, PersonalData $personalData): PersonalData
    {
        $personalData->load($this->request->post());
        if ($this->request->post('user_email') && $currentUser->email != $this->request->post('user_email')) {
            $validator = new EmailValidator();
            if ($validator->validate($this->request->post('user_email'))) {
                $currentUser->email = $this->request->post('user_email');
                $currentUser->save(false);
            }
        }

        return $personalData;
    }
}
