<?php

namespace common\services\abiturientController\bachelor\accounting_benefits;

use common\components\configurationManager;
use common\components\ReferenceTypeManager\ContractorManager;
use common\components\RegulationRelationManager;
use common\models\AttachmentType;
use common\models\dictionary\DocumentType;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use yii\base\UserException;
use yii\caching\CacheInterface;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Request;

class TargetReceptionsService extends AccountingBenefitsService
{
    




    public function __construct(
        Request $request,
        CacheInterface $cache,
        configurationManager $configurationManager
    ) {
        parent::__construct($request, $cache, $configurationManager);
    }

    






    public function getTargets(int $appId)
    {
        $docTypeUid = $this->configurationManager->getCode('target_reception_document_type_guid');
        $items = $this->getDocumentItems($docTypeUid);

        $application = $this->getApplication($appId);
        $model = $this->initTargetReception($application);

        $targets = $application
            ->getTargetReceptions()
            ->all();

        $dataProvider = new ArrayDataProvider([
            'allModels' => $targets,
            'pagination' => ['pageSize' => 10]
        ]);

        $canEdit = $this->canEditTarget($application);

        return [
            'id' => $appId,
            'model' => $model,
            'items' => $items,
            'canEdit' => $canEdit,
            'providers' => $targets,
            'dataProvider' => $dataProvider,
            'action' => '/site/target-reception'
        ];
    }

    








    public function editTarget(User $currentUser): array
    {
        $formName = (new BachelorTargetReception)->formName();
        [
            'id' => $id,
            'appId' => $appId,
            'application' => $application,
        ] = $this->editAccountingBenefits($formName);

        $accountingBenefits = $this->getAccountingBenefitsQueryForEditFunction(
            $id,
            $appId,
            BachelorTargetReception::class
        )
            ->notInEnlistedApp()
            ->one();

        if (isset($accountingBenefits)) {
            return $this->updateTargetReceptionFromPost(
                $currentUser,
                $application,
                $accountingBenefits
            );
        }

        return [null, false, false];
    }

    







    public function saveNewTargets(User $currentUser, ?int $id): array
    {
        $formName = (new BachelorTargetReception)->formName();
        $application = $this->saveNewAccountingBenefits($id, $formName);
        $newAccountingBenefits = $this->initTargetReception($application);

        return $this->updateTargetReceptionFromPost(
            $currentUser,
            $application,
            $newAccountingBenefits
        );
    }

    








    public function downloadTargets(User $currentUser, ?int $id): array
    {
        return $this->generateFilesToDownloadAccountingBenefits(
            $currentUser,
            $id,
            BachelorTargetReception::class,
            function ($_, $collection) {
                return "Целевой договор {$collection->getAttachmentTypeName()}.zip";
            },
            'Не удалось найти целевой договор.'
        );
    }

    






    public function canDownloadTargetReception(?int $id = null): bool
    {
        return $this->canGenerateFilesToDownloadAccountingBenefits($id, BachelorTargetReception::class);
    }

    






    public function updateTargetReceptionFromPost(
        User $currentUser,
        BachelorApplication $application,
        BachelorTargetReception $target
    ): array {
        return $this->updateAccountingBenefitsFromPost(
            $currentUser,
            $application,
            $target,
            function ($target) {
                return $target;
            },
            'Ошибка при редактировании целевого приёма:'
        );
    }

    




    public function getRegulationsAndAttachmentsForTarget(BachelorApplication $application): array
    {
        return $this->getRegulationsAndAttachments(
            $application,
            AttachmentType::RELATED_ENTITY_TARGET_RECEPTION,
            RegulationRelationManager::RELATED_ENTITY_TARGET_RECEPTION
        );
    }

    








    public function archiveTargerReceprion(?int $id, User $currentUser, bool $updateApplicationHistory = true): void
    {
        $this->archiveAccountingBenefit(
            $id,
            $currentUser,
            BachelorTargetReception::class,
            'Невозможно удалить файл целевого договора.',
            'Невозможно удалить целевой договор.',
            $updateApplicationHistory
        );
    }

    




    protected function initTargetReception(BachelorApplication $application): BachelorTargetReception
    {
        $model = new BachelorTargetReception();
        $docTypeUid = $this->configurationManager->getCode('target_reception_document_type_guid');
        if ($docTypeUid) {
            $targetReceptionDocType = DocumentType::findByUID($docTypeUid);
            $model->document_type = ArrayHelper::getValue($targetReceptionDocType, 'code');
            $model->document_type_id = ArrayHelper::getValue($targetReceptionDocType, 'id');
        }
        $model->id_application = $application->id;

        return $model;
    }

    




    private function canEditTarget(BachelorApplication $application): bool
    {
        $targets_count = $application
            ->getTargetReceptions()
            ->count();

        return $targets_count < BachelorTargetReception::NUMBER_OF_TARGET_RECEPTION &&
            $application->canEdit() &&
            $application->canEditSpecialities();
    }

    



    protected function updateContractorFromPost($accountingBenefitsFrom): void
    {
        if ($accountingBenefitsFrom->not_found_document_contractor) {
            $accountingBenefitsFrom->document_contractor_id = ContractorManager::Upsert(
                $this->request->post('DocumentContractor'),
                $accountingBenefitsFrom->documentType
            )->id;
        }
        
        if ($accountingBenefitsFrom->not_found_target_contractor) {
            $accountingBenefitsFrom->target_contractor_id = ContractorManager::Upsert(
                $this->request->post('TargetContractor'),
                $accountingBenefitsFrom->documentType
            )->id;
        }
    }
}
