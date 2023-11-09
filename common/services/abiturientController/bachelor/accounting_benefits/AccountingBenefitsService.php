<?php

namespace common\services\abiturientController\bachelor\accounting_benefits;

use Closure;
use common\components\AttachmentManager;
use common\components\configurationManager;
use common\components\filesystem\FilterFilename;
use common\components\ReferenceTypeManager\ContractorManager;
use common\models\Attachment;
use common\models\dictionary\AvailableDocumentTypesForConcession;
use common\models\dictionary\DocumentType;
use common\models\dictionary\DocumentTypesForConcessionJunctionToFilters;
use common\models\dictionary\Privilege;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredAvailableDocumentTypeFilterReferenceType;
use common\models\ModelFrom1CByOData;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\services\abiturientController\bachelor\BachelorService;
use Throwable;
use Yii;
use yii\base\UserException;
use yii\caching\CacheInterface;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use ZipArchive;

class AccountingBenefitsService extends BachelorService
{
    
    protected CacheInterface $cache;

    




    public function __construct(
        Request $request,
        CacheInterface $cache,
        configurationManager $configurationManager
    ) {
        $this->cache = $cache;

        parent::__construct($request, $configurationManager);
    }

    










    public function postProcessingRegulationsAndAttachments(
        BachelorApplication $application,
        array               $attachments,
        array               $regulations
    ): array {
        if ($this->request->post('UserRegulation') !== null) {
            return parent::postProcessingRegulationsAndAttachments($application, $attachments, $regulations);
        }

        return [
            'hasChanges' => false,
            'attachments' => $attachments,
            'regulations' => $regulations,
        ];
    }

    





    public function getNextStep(BachelorApplication $application, string $currentStep = 'accounting-benefits'): string
    {
        return parent::getNextStep($application, $currentStep);
    }

    







    public function canGenerateFilesToDownloadAccountingBenefits(?int $id = null, string $accountingBenefitsClass): bool
    {
        AccountingBenefitsService::checkIsCorrectAccountingBenefitsClass($accountingBenefitsClass);

        if (isset($id)) {
            $model = $accountingBenefitsClass::findOne($id);
            if (isset($model)) {
                return $model->getAttachments()->exists();
            }
        }

        return false;
    }

    





    protected function getDocTypesByBachelorPreferenceCodeAndBachelorApplication(BachelorApplication $application, ?ModelFrom1CByOData $preference)
    {
        $ValueArray = null;
        if ($preference) {
            if ($preference instanceof Privilege) {
                $subject_type = DocumentTypesForConcessionJunctionToFilters::SUBJECT_TYPE_PRIVILEGES;
            } else {
                $subject_type = DocumentTypesForConcessionJunctionToFilters::SUBJECT_TYPE_SPECIAL_MARKS;
            }

            $id_subject = $preference->ref_key;

            $tnFilterJunctions = DocumentTypesForConcessionJunctionToFilters::tableName();
            $tnAvailableDocumentTypeFilterRef = StoredAvailableDocumentTypeFilterReferenceType::tableName();
            $query = AvailableDocumentTypesForConcession::find()
                ->joinWith('admissionCampaignRef', false)
                ->joinWith('documentTypeRef', false)
                ->joinWith(['filterJunctions'])
                ->joinWith('availableDocumentTypeFilterRef')
                ->andWhere(["{$tnFilterJunctions}.subject_type" => $subject_type])
                ->andWhere(["{$tnAvailableDocumentTypeFilterRef}.reference_uid" => $id_subject])
                ->andWhere([StoredAdmissionCampaignReferenceType::tableName() . '.reference_uid' => $application->type->rawCampaign->referenceType->reference_uid])
                ->andWhere(['dictionary_available_document_types_for_concession.archive' => false])
                ->select(DocumentType::tableName() . '.ref_key');

            $ValueArray = DocumentType::find()
                ->notMarkedToDelete()
                ->active()
                ->select(['maxid' => 'max(dictionary_document_type.id)', 'dictionary_document_type.ref_key', 'dictionary_document_type.description'])
                ->andWhere(['dictionary_document_type.ref_key' => $query])
                ->andWhere(['dictionary_document_type.is_folder' => false])
                ->groupBy(['dictionary_document_type.ref_key', 'dictionary_document_type.description'])
                ->orderBy('dictionary_document_type.description')
                ->asArray()
                ->all();
        }

        
        if (empty($ValueArray)) {
            $ValueArray = DocumentType::find()
                ->notMarkedToDelete()
                ->active()
                ->select(['maxid' => 'max(dictionary_document_type.id)', 'dictionary_document_type.ref_key', 'dictionary_document_type.description'])
                ->andWhere(['dictionary_document_type.is_folder' => false])
                ->groupBy(['dictionary_document_type.ref_key', 'dictionary_document_type.description'])
                ->orderBy('dictionary_document_type.description')
                ->asArray()
                ->all();
        }

        return $ValueArray;
    }

    





    protected function initBachelorPreferences(BachelorApplication $application, string $type): BachelorPreferences
    {
        $model = new BachelorPreferences();
        $model->id_application = $application->id;
        $model->setPreferenceType($type);

        return $model;
    }

    




    protected function getDocumentItems(?string $docTypeUid = null): array
    {
        $tnDocumentType = DocumentType::tableName();
        $docsQuery = DocumentType::find()
            ->notMarkedToDelete()
            ->active()
            ->andWhere(['is_folder' => false])
            ->orderBy("{$tnDocumentType}.description");

        if ($docTypeUid) {
            $docsQuery->andWhere(["{$tnDocumentType}.ref_key" => $docTypeUid]);
        }

        return ArrayHelper::map($docsQuery->all(), 'id', 'description');
    }

    








    protected function editAccountingBenefits(string $formName)
    {
        if ($this->request->isPost) {
            $appId = $this->request->post($formName)['id_application'];
            $id = $this->request->post($formName)['id'];
            if ($appId) {
                $application = $this->getApplication($appId);

                return [
                    'id' => $id,
                    'appId' => $appId,
                    'application' => $application,
                ];
            }
        }
        throw new UserException('Не переданы необходимые параметры');
    }

    








    protected function getAccountingBenefitsQueryForEditFunction(
        int $id,
        int $appId,
        string $accountingBenefitsClass
    ): ActiveQuery {
        AccountingBenefitsService::checkIsCorrectAccountingBenefitsClass($accountingBenefitsClass);

        $tnAccountingBenefits = $accountingBenefitsClass::tableName();

        return $accountingBenefitsClass::find()
            ->andWhere([
                "{$tnAccountingBenefits}.id" => $id,
                "{$tnAccountingBenefits}.id_application" => $appId,
            ]);
    }

    







    protected function saveNewAccountingBenefits(
        ?int   $id,
        string $formName
    ): BachelorApplication {
        if ($this->request->isPost) {
            if (!$id) {
                $id = $this->request->post($formName)['id_application'];
            }
            return $this->getApplication($id);
        }
        throw new UserException('Не переданы необходимые параметры');
    }

    











    protected function generateFilesToDownloadAccountingBenefits(
        User    $currentUser,
        ?int    $id,
        string  $accountingBenefitClass,
        Closure $fileNameCallback,
        string  $throwMessage
    ): array {
        AccountingBenefitsService::checkIsCorrectAccountingBenefitsClass($accountingBenefitClass);

        $accountingBenefit = $accountingBenefitClass::findOne((int)$id);
        if ($accountingBenefit != null) {
            $zip = new ZipArchive();

            $collection = $accountingBenefit->attachmentCollection;
            $filename = FilterFilename::sanitize($fileNameCallback($accountingBenefit, $collection));

            if ($zip->open(Yii::getAlias("@storage/web/tempZip/{$filename}"), ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new UserException('Не удалось создать архив.');
            }

            foreach ($collection->attachments as $key => $attachment) {
                

                if ($attachment->checkAccess($currentUser)) {
                    $path = $attachment->getAbsPath();
                    if ($path && file_exists($path)) {
                        $number = $key + 1;
                        $zip->addFile(
                            $path,
                            FilterFilename::sanitize("{$number}. " .
                                $attachment->getAttachmentTypeName() .
                                '.' .
                                $attachment->extension)
                        );
                    }
                }
            }
            if ($zip->numFiles > 0) {
                $pathToZipArchive = $zip->filename;
                $zip->close();

                return [
                    'filename' => $filename,
                    'pathToZipArchive' => $pathToZipArchive
                ];
            } else {
                throw new UserException('Нет файлов для отправки.');
            }
        }
        throw new NotFoundHttpException($throwMessage);
    }

    








    protected function updateAccountingBenefitsFromPost(
        User                $currentUser,
        BachelorApplication $application,
        $accountingBenefitsFrom,
        Closure             $beforeSaveCallback,
        string              $errorMessageTemplate
    ): array {
        $saveSuccess = false;
        $hasChangedAttributes = false;
        if ($accountingBenefitsFrom->load($this->request->post())) {
            $this->updateContractorFromPost($accountingBenefitsFrom);

            $accountingBenefitsFrom = $beforeSaveCallback($accountingBenefitsFrom);
            $hasChangedAttributes = $accountingBenefitsFrom->hasChangedAttributes();
            if ($accountingBenefitsFrom->save()) {
                if (!$currentUser->isModer()) {
                    $application->resetStatus();
                }
                $attachedFileHashList = $accountingBenefitsFrom->buildAttachmentHash();
                AttachmentManager::handleAttachmentUpload([$accountingBenefitsFrom->attachmentCollection]);
                $saveSuccess = true;

                if (!$accountingBenefitsFrom->checkIfDocumentIsChanged($attachedFileHashList)) {
                    $accountingBenefitsFrom->setDocumentCheckStatusNotVerified();
                    $accountingBenefitsFrom->save(['document_check_status_ref_id']);

                    $hasChangedAttributes = true;
                }
            } else {
                $log = ['data' => [
                    'id' => $accountingBenefitsFrom->id,
                    'id_application' => $application->id,
                    'formName' => $accountingBenefitsFrom->formName(),
                ]];
                Yii::error(
                    $errorMessageTemplate .
                        PHP_EOL .
                        print_r($accountingBenefitsFrom->errors, true) .
                        PHP_EOL .
                        print_r($log, true),
                    'AccountingBenefitsService.updateAccountingBenefitsFromPost'
                );
            }
        }

        return [$accountingBenefitsFrom, $saveSuccess, $hasChangedAttributes];
    }

    
















    protected function archiveAccountingBenefit(
        ?int   $id = null,
        User   $currentUser,
        string $accountingBenefitsClass,
        string $errorMessageOnFileDeletion,
        string $errorMessageOnAccountingBenefitArchiving,
        bool   $updateApplicationHistory = true
    ): void {
        if (isset($id)) {
            AccountingBenefitsService::checkIsCorrectAccountingBenefitsClass($accountingBenefitsClass);

            $tnAccountingBenefits = $accountingBenefitsClass::tableName();
            $model = $accountingBenefitsClass::find()
                ->notInEnlistedApp()
                ->andWhere(["{$tnAccountingBenefits}.id" => $id])
                ->one();

            if (isset($model) && !$model->read_only) {
                $db = $accountingBenefitsClass::getDb();
                $transaction = $db->beginTransaction();
                if (!isset($transaction)) {
                    throw new UserException('Невозможно начать транзакцию');
                }
                try {
                    foreach ($model->attachments as $attachment) {
                        if (!$attachment->safeDelete($currentUser, $updateApplicationHistory)) {
                            throw new UserException($errorMessageOnFileDeletion);
                        }
                    }
                    if (!$model->archive()) {
                        throw new UserException($errorMessageOnAccountingBenefitArchiving);
                    }

                    if (!$currentUser->isModer()) {
                        $model->application->resetStatus();
                    }
                    $transaction->commit();
                } catch (Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }
    }

    






    private static function checkIsCorrectAccountingBenefitsClass(string $accountingBenefitsClass): bool
    {
        $correctAccountingBenefitsClasses = [
            BachelorPreferences::class,
            BachelorTargetReception::class,
        ];
        if (in_array(
            $accountingBenefitsClass,
            $correctAccountingBenefitsClasses
        )) {
            return true;
        }

        throw new UserException("Был передан класс не относящийся к категории «особых прав» ({$accountingBenefitsClass})");
    }

    



    protected function updateContractorFromPost($accountingBenefitsFrom): void
    {
        if ($accountingBenefitsFrom->notFoundContractor) {
            $accountingBenefitsFrom->contractor_id = ContractorManager::Upsert(
                $this->request->post('Contractor'),
                $accountingBenefitsFrom->documentType
            )->id;
        }
    }
}
