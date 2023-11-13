<?php

namespace common\services\abiturientController\bachelor;

use common\components\AttachmentManager;
use common\components\EducationAndEntranceTestsManager\EducationAndEntranceTestsManager;
use common\components\EducationDocumentManager\EducationDocumentManager;
use common\components\EntrantTestManager\EntrantTestManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\components\RegulationRelationManager;
use common\models\attachment\attachmentCollection\ApplicationAttachmentCollection;
use common\models\AttachmentType;
use common\models\dictionary\DocumentType;
use common\models\dictionary\EducationDataFilter;
use common\models\dictionary\EducationType;
use common\models\dictionary\StoredReferenceType\StoredEducationLevelReferenceType;
use common\models\EmptyCheck;
use common\models\UserRegulation;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EducationData;
use common\services\abiturientController\bachelor\BachelorService;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class EducationService extends BachelorService
{
    




    public function getRegulationsAndAttachmentsForEducation(BachelorApplication $application): array
    {
        return $this->getRegulationsAndAttachments(
            $application,
            AttachmentType::RELATED_ENTITY_EDUCATION,
            RegulationRelationManager::RELATED_ENTITY_EDUCATION
        );
    }

    










    public function postProcessingRegulationsAndAttachments(
        BachelorApplication $application,
        array               $attachments,
        array               $regulations
    ): array {
        if (
            $application->canEdit() ||
            $application->hasPassedApplicationWithEditableAttachments(AttachmentType::RELATED_ENTITY_EDUCATION)
        ) {
            return parent::postProcessingRegulationsAndAttachments($application, $attachments, $regulations);
        }

        return [
            'hasChanges' => false,
            'attachments' => $attachments,
            'regulations' => $regulations,
        ];
    }

    




    public function getFileControlFlags(BachelorApplication $application): array
    {
        $allowAddNewEducationAfterApprove = true;
        $allowAddNewFileToEducationAfterApprove = true;
        $allowDeleteFileFromEducationAfterApprove = true;
        $hasPassedApplication = $application->hasPassedApplication();
        if ($hasPassedApplication) {
            $allowAddNewEducationAfterApprove = ArrayHelper::getValue($application, 'type.allow_add_new_education_after_approve', false);
            $allowAddNewFileToEducationAfterApprove = ArrayHelper::getValue($application, 'type.allow_add_new_file_to_education_after_approve', false);
            $allowDeleteFileFromEducationAfterApprove = ArrayHelper::getValue($application, 'type.allow_delete_file_from_education_after_approve', false);
        }

        return [
            'allowAddNewEducationAfterApprove' => $allowAddNewEducationAfterApprove,
            'allowAddNewFileToEducationAfterApprove' => $allowAddNewFileToEducationAfterApprove,
            'allowDeleteFileFromEducationAfterApprove' => $allowDeleteFileFromEducationAfterApprove,
        ];
    }

    






    public function checkAttachmentFiles(
        BachelorApplication $application,
        bool                $canEdit,
        ?string             $attachmentTypeRelatedEntity = null
    ): array {
        return parent::checkAttachmentFiles(
            $application,
            $canEdit,
            AttachmentType::RELATED_ENTITY_EDUCATION
        );
    }

    





    public function getNextStep(BachelorApplication $application, string $currentStep = 'education'): string
    {
        return parent::getNextStep($application, $currentStep);
    }

    







    public function getEducation(BachelorApplication $application, ?int $id): EducationData
    {
        $education = null;
        if (is_null($id)) {
            $education = new EducationData();
            $education->application_id = $application->id;

            return $education;
        }

        $education = $application->getEducations()
            ->andWhere(['id' => $id])
            ->limit(1)
            ->one();
        if (empty($education)) {
            throw new NotFoundHttpException('Данные об образовании не найдены');
        }

        return $education;
    }

    





    public function deleteEducation(BachelorApplication $application, int $id): void
    {
        if (EducationDocumentManager::DeleteEducationDocument($application, $id)) {
            $application->resetStatus();
            $application->addApplicationHistory(ApplicationHistory::TYPE_EDUCATION_CHANGED);
        }
    }

    













    public function educationSaveProcess(
        BachelorApplication $application,
        EducationData       $education,
        bool                $isManager
    ): array {
        $educationSaved = false;
        $hasChangedAttributes = $education->hasChangedAttributes();

        $db = EducationData::getDb();
        $transaction = $db->beginTransaction();
        try {
            if ($education->save(true)) {
                $attachedFileHashList = $education->buildAttachmentHash();
                AttachmentManager::handleAttachmentUpload([$education->attachmentCollection]);

                if (!$education->checkIfDocumentIsChanged($attachedFileHashList)) {
                    $education->setDocumentCheckStatusNotVerified();
                    $education->save(['document_check_status_ref_id']);

                    $hasChangedAttributes = true;
                }
                if (EducationAndEntranceTestsManager::hasDifferenceBetweenOldAndNewAttributes($education)) {
                    $testSetToArchive = EducationAndEntranceTestsManager::getRelatedEntrantTestSetsQuery($education, $application);
                    try {
                        EntrantTestManager::archiveNotActualEntranceTestSetExceptReadOnly(
                            $application,
                            $testSetToArchive,
                            'IN'
                        );
                    } catch (UserException $th) {
                        Yii::error("Ошибка актуализации набора ВИ: {$th->getMessage()}", 'EducationService.educationSaveProcess');
                    }
                }

                if (!$isManager) {
                    $application->addApplicationHistory(ApplicationHistory::TYPE_EDUCATION_CHANGED);
                    $application->resetStatus();
                }

                $educationSaved = true;
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollback();
            throw $e;
        }

        return [
            'education' => $education,
            'educationSaved' => $educationSaved,
            'hasChangedAttributes' => $hasChangedAttributes,
        ];
    }

    





    public function afterEducationSaveProcessAsNotModerator(
        BachelorApplication $application,
        EducationData       $education
    ): string {
        if (!$application->specialities) {
            return '';
        }
        $educationDescriptionString = $education->getDescriptionString();
        $link = Html::a(
            Yii::t(
                'abiturient/bachelor/education/all',
                'Подпись ссылки для перехода на вкладку с ВИ, для сообщения о сбросе наборов ВИ; на странице док. об образ.: `вступительных испытаний`'
            ),
            ['bachelor/ege', 'id' => $application->id]
        );

        return Yii::t(
            'abiturient/bachelor/education/all',
            'Текст сообщения о том что у заявления сброшены наборы ВИ из-за смены профиля образования; на странице док. об образ.: `<strong>Внимание!</strong> Для вступительных испытаний, у которых в направлении подготовки, было указанно образование "{educationDescriptionString}", сброшены наборы вступительных испытаний.<br />Пожалуйста, перейдите на вкладку {link} и подтвердите наборы повторно.`',
            [
                'link' => $link,
                'educationDescriptionString' => $educationDescriptionString,
            ]
        );
    }

    


    public function getEducationLevelsDataForSelect(): array
    {
        $output = [];
        $params = $this->request->post('depdrop_params');

        if (!empty($params) && isset($params[0])) {
            $edu_type = null;
            $typeId = $params[0] ?? null;
            if (!EmptyCheck::isLoadingStringOrEmpty($typeId)) {
                $edu_type = EducationType::findOne($typeId);
            }
            if (!empty($edu_type)) {
                $tnStoredEducationLevelReferenceType = StoredEducationLevelReferenceType::tablename();
                $items = EducationDataFilter::find()
                    ->joinWith(['educationLevelRef'])
                    ->where(['education_type_id' => $edu_type->id])
                    ->andWhere(['not', ["{$tnStoredEducationLevelReferenceType}." . StoredEducationLevelReferenceType::getDeletionMarkColumnName() => true]])
                    ->andWhere(["{$tnStoredEducationLevelReferenceType}.is_folder" => false])
                    ->orderBy("{$tnStoredEducationLevelReferenceType}.reference_name")
                    ->all();

                $output = $this->makeDataFormattedForDepDrop(
                    function ($item) {
                        return [
                            'id' => ArrayHelper::getValue($item->educationLevelRef, 'id'),
                            'name' => ArrayHelper::getValue($item->educationLevelRef, 'reference_name'),
                        ];
                    },
                    $items
                );
            }
        }

        return array_values(ArrayHelper::index($output, 'id'));
    }

    


    public function getEducationDocsDataForSelect(): array
    {
        $output = [];
        $params = $this->request->post('depdrop_params');

        if (!empty($params) && (isset($params[0]) || isset($params[1]))) {
            $edu_type = null;
            $type_id = $params[0] ?? null;
            if (!EmptyCheck::isLoadingStringOrEmpty($type_id)) {
                $edu_type = EducationType::findOne($type_id);
            }

            $edu_level = null;
            $level_id = $params[1] ?? null;
            if (!EmptyCheck::isLoadingStringOrEmpty($level_id)) {
                $edu_level = StoredEducationLevelReferenceType::findOne($level_id);
            }
            if (!empty($edu_type) || !empty($edu_level)) {
                $tnDocumentType = DocumentType::tableName();
                $items = EducationDataFilter::find()
                    ->joinWith(['documentTypeRef'])
                    ->andFilterWhere(['education_type_id' => ArrayHelper::getValue($edu_type, 'id')])
                    ->andFilterWhere(['education_level_id' => ArrayHelper::getValue($edu_level, 'id')])
                    ->andWhere(['not', ["{$tnDocumentType}." . DocumentType::getDeletionMarkColumnName() => true]])
                    ->andWhere(["{$tnDocumentType}.is_folder" => false])
                    ->orderBy("{$tnDocumentType}.description")
                    ->all();

                $output = $this->makeDataFormattedForDepDrop(
                    function ($item) {
                        return [
                            'id' => ArrayHelper::getValue($item->documentTypeRef, 'id'),
                            'name' => ArrayHelper::getValue($item->documentTypeRef, 'description'),
                            'options' => ['data-code' => ArrayHelper::getValue($item->documentTypeRef, 'ref_key')],
                        ];
                    },
                    $items
                );
            }
        }

        return array_values(ArrayHelper::index($output, 'id'));
    }

    


    public function getEducationProfileDataForSelect(): array
    {
        $output = [];
        $selectedProfile = '';

        $params = $this->request->post('depdrop_params');
        if (!empty($params) && (isset($params[0]) || isset($params[1]) || isset($params[2]) || isset($params[3]))) {
            $eduType = null;
            $typeId = $params[0] ?? null;
            if (!EmptyCheck::isLoadingStringOrEmpty($typeId)) {
                $eduType = EducationType::findOne($typeId);
            }

            $eduLevel = null;
            $levelId = $params[1] ?? null;
            if (!EmptyCheck::isLoadingStringOrEmpty($levelId)) {
                $eduLevel = StoredEducationLevelReferenceType::findOne($levelId);
            }

            $eduDocType = null;
            $docTypeId = $params[2] ?? null;
            if (!EmptyCheck::isLoadingStringOrEmpty($docTypeId)) {
                $eduDocType = DocumentType::findOne($docTypeId);
            }

            $selectedProfile = null;
            if (!EmptyCheck::isLoadingStringOrEmpty($params[3])) {
                $selectedProfile = $params[3];
            }
            if (!empty($eduType) || !empty($eduLevel)) {
                $eduFilterHas = EducationDataFilter::find()
                    ->andFilterWhere(['allow_profile_input' => true])
                    ->andFilterWhere(['education_type_id'   => ArrayHelper::getValue($eduType, 'id')])
                    ->andFilterWhere(['education_level_id'  => ArrayHelper::getValue($eduLevel, 'id')])
                    ->andFilterWhere(['document_type_id'    => ArrayHelper::getValue($eduDocType, 'id')])
                    ->exists();

                if ($eduFilterHas) {
                    $items = EducationData::getRawProfileList();
                    $output = $this->makeDataFormattedForDepDrop(
                        function ($item) {
                            return [
                                'id' => $item->id,
                                'name' => $item->reference_name,
                            ];
                        },
                        $items
                    );
                }
            }
        }

        return [
            'output' =>  array_values(ArrayHelper::index($output, 'id')),
            'selected' => $selectedProfile
        ];
    }

    public function setContractor(EducationData $education)
    {
        if ($education->notFoundContractor) {
            $education->contractor_id = ContractorManager::Upsert($this->request->post('Contractor'), $education->documentType)->id;
        }
    }
}
