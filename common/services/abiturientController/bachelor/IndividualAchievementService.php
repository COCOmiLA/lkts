<?php

namespace common\services\abiturientController\bachelor;

use common\components\AttachmentManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\models\dictionary\IndividualAchievementType;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\AdmissionCampaign;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\repositories\IndividualAchievementDocumentTypesRepository;
use common\services\abiturientController\bachelor\BachelorService;
use yii\base\UserException;
use yii\helpers\ArrayHelper;

class IndividualAchievementService extends BachelorService
{
    




    public function getIndividualAchievementDocumentTypesDataForSelect(): array
    {
        $output = [];
        $selectedProfile = '';

        $parents = $this->request->post('depdrop_parents');
        if ($parents != null && $parents[0] != '') {
            $individualAchievementTypeId = (int)$parents[0];
            $individualAchievementType = IndividualAchievementType::findOne($individualAchievementTypeId);
            if (!is_null($individualAchievementType)) {
                if (is_null($individualAchievementType->admissionCampaignRef)) {
                    throw new UserException("Для  строки справочника индивидуальных достижений ({$individualAchievementType->id}) не задана ссылка на приемную кампанию, пожалуйста обновите справочники.");
                }

                $campaign = AdmissionCampaign::find()
                    ->joinWith('referenceType reference_type')
                    ->andWhere(['reference_type.reference_uid' => $individualAchievementType->admissionCampaignRef->reference_uid])
                    ->one();

                if (is_null($campaign)) {
                    throw new UserException("Не найдена приемная кампания по ссылке({$individualAchievementType->admissionCampaignRef->id}): \n" . print_r([
                        'ReferenceId' => $individualAchievementType->admissionCampaignRef->reference_id,
                        'ReferenceUID' => $individualAchievementType->admissionCampaignRef->reference_uid,
                        'ReferenceName' => $individualAchievementType->admissionCampaignRef->reference_name,
                        'ReferenceClassName' => $individualAchievementType->admissionCampaignRef->reference_class_name
                    ], true));
                }
                $chosenDocumentType = null;
                if (isset($parents[1])) {
                    $individualAchievement = IndividualAchievement::findOne($parents[1]);
                    if ($individualAchievement) {
                        $chosenDocumentType = $individualAchievement->documentType;
                    }
                }
                $docTypes = IndividualAchievementDocumentTypesRepository::GetDocumentTypesByIndividualAchievementTypeAndCampaign($campaign->referenceType, $individualAchievementType, $chosenDocumentType);
                if ($docTypes) {
                    $selectedProfile = $chosenDocumentType->id ?? $docTypes[0]->id;
                    $output = $this->makeDataFormattedForDepDrop(
                        function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->documentDescription,
                                'options' => [
                                    'data-scan_required' => $item->scan_required,
                                    'data-document_type_id' => $item->document_type_ref_id
                                ]
                            ];
                        },
                        $docTypes
                    );
                }
            }
        }

        return [
            'output' => array_values(ArrayHelper::index($output, 'id')),
            'selected' => $selectedProfile
        ];
    }

    






    public function getOrCrateIndividualAchievement(
        User                $currentUser,
        BachelorApplication $application,
        ?int                $id
    ): IndividualAchievement {
        $individualAchievement = new IndividualAchievement();
        if (isset($id)) {
            $individualAchievement = IndividualAchievement::findOne(['id' => $id]);
        }

        $individualAchievement->load($this->request->post());
        $individualAchievement->isFrom1C = false;
        $individualAchievement->status = IndividualAchievement::STATUS_UNSTAGED;
        $individualAchievement->user_id = $currentUser->id;
        $individualAchievement->application_id = $application->id;

        return $individualAchievement;
    }

    





    public function fillFromEducationData(
        BachelorApplication   $application,
        IndividualAchievement $individualAchievement
    ): bool {
        if (!$education_id = $this->request->post('fill_from_education')) {
            return false;
        }

        
        $education = $application
            ->getRawEducations()
            ->andWhere(['id' => $education_id])
            ->one();
        if (!$education) {
            
            return false;
        }

        $individualAchievement->fillFromEducation($education);
        return true;
    }

    







    public function savingProcess(
        BachelorApplication   $application,
        IndividualAchievement $individualAchievement
    ): bool {
        if ($individualAchievement->not_found_contractor) {
            $individualAchievement->contractor_id = ContractorManager::Upsert(
                $this->request->post('Contractor'),
                $individualAchievement->realDocumentType
            )->id;
        }

        $hasChangedAttributes = $individualAchievement->hasChangedAttributes();
        if (!$individualAchievement->save()) {
            throw new RecordNotValid($individualAchievement);
        }
        $attachedFileHashList = $individualAchievement->buildAttachmentHash();
        AttachmentManager::handleAttachmentUpload([$individualAchievement->attachmentCollection]);

        if (!$individualAchievement->checkIfDocumentIsChanged($attachedFileHashList)) {
            $individualAchievement->setDocumentCheckStatusNotVerified();
            $individualAchievement->save(['document_check_status_ref_id']);

            $hasChangedAttributes = true;
        }

        if ($application) {
            $application->addApplicationHistory(ApplicationHistory::TYPE_INDIVIDUAL_ACH_CHANGED);
            $application->resetStatus();
        }

        return $hasChangedAttributes;
    }
}
