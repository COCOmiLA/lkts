<?php

namespace common\services\abiturientController\questionary;

use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\models\dictionary\DocumentType;
use common\models\dictionary\FamilyType;
use common\models\EmptyCheck;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\parentData\ParentAddressData;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\parentData\ParentPassportData;
use common\modules\abiturient\models\parentData\ParentPersonalData;
use common\services\abiturientController\questionary\AbiturientQuestionaryService;
use yii\base\UserException;
use yii\helpers\ArrayHelper;



class ParentDataService extends AbiturientQuestionaryService
{
    




    public function getOrBuildParentData(
        User                  $currentUser,
        AbiturientQuestionary $questionary,
        string                $path
    ): ParentData {
        $id = ArrayHelper::getValue($this->request->post(), $path);

        $model = new ParentData();
        $model->questionary_id = $questionary->id;
        if (!EmptyCheck::isEmpty($id)) {
            $model = ParentData::findOne($id);
            $this->checkAccessibility($currentUser, $model->questionary_id);
        }

        return $model;
    }

    


    public function getFamilyTypes(): array
    {
        return FamilyType::find()
            ->where(['archive' => false])
            ->orderBy('name')
            ->all();
    }

    


    public function getDocumentTypeID(): ?int
    {
        if ($documentTypeEntity = CodeSettingsManager::GetEntityByCode('russian_passport_guid')) {
            return $documentTypeEntity->id;
        }

        return null;
    }

    


    public function getAllIdentityDocuments(): array
    {
        $uid = $this->configurationManager->getCode('identity_docs_guid');

        $parent = DocumentType::findByUID($uid);
        if (!$parent) {
            return [];
        }
        $docs = DocumentType::find()
            ->andWhere(['parent_key' => $parent->ref_key])
            ->orderBy(['ref_key' => SORT_DESC])
            ->andWhere(['is_folder' => false])
            ->notMarkedToDelete()
            ->active()
            ->all();

        return ArrayHelper::map($docs, 'id', 'description');
    }

    











    public function loadParentData(ParentData $parentData): array
    {
        $personalData = $parentData->personalData ?? new ParentPersonalData();
        $addressData = $parentData->addressData ?? new ParentAddressData();
        $passportData = $parentData->passportData ?? new ParentPassportData();

        if (
            !$personalData->load($this->request->post()) ||
            !$parentData->load($this->request->post())
        ) {
            throw new UserException('Ошибка загрузки обязательных моделей');
        }
        
        $addressData->load($this->request->post());
        $passportData->load($this->request->post());

        return [
            'parentData' => $parentData,
            'addressData' => $addressData,
            'passportData' => $passportData,
            'personalData' => $personalData,
        ];
    }

    








    public function setParentData(
        User               $currentUser,
        ParentData         $parentData,
        ParentPassportData $passportData,
        ParentPersonalData $personalData,
        ParentAddressData  $addressData
    ): bool {
        if ($passportData->notFoundContractor) {
            $passportData->contractor_id = ContractorManager::Upsert($this->request->post('Contractor'), $passportData->documentType)->id;
        }

        $personalData->setUserForInitialization($currentUser);
        $addressData->setUserForInitialization($currentUser);
        $passportData->setUserForInitialization($currentUser);

        $addressData->processAddressDataFromPost();

        $valid = $personalData->validate() &&
            $addressData->validate() &&
            $passportData->validate();

        $success = $valid && $personalData->save() && $addressData->save() && $passportData->save();

        if ($success) {
            $parentData->personal_data_id = $personalData->id;
            $parentData->address_data_id = $addressData->id;
            $parentData->passport_data_id = $passportData->id;

            $success = $parentData->save();
        }

        foreach ($currentUser->getApplications()
            ->andWhere([BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_CREATED])
            ->all() as $application) {
            if (!$application->resetStatus()) {
                $success = false;
            }

            if (!$application->addApplicationHistory(ApplicationHistory::TYPE_PARENT_DATA_CHANGED)) {
                $success = false;
            }
        }

        return $success;
    }

    


    public function parentDataChangedEvent(User $currentUser): void
    {
        $tnBachelorApplication = BachelorApplication::tableName();
        $applications = $currentUser->getApplications()
            ->andWhere(["{$tnBachelorApplication}.draft_status" => IDraftable::DRAFT_STATUS_CREATED])
            ->all();

        foreach ($applications as $application) {
            $application->resetStatus();
            $application->addApplicationHistory(ApplicationHistory::TYPE_PARENT_DATA_CHANGED);
        }
    }
}
