<?php

namespace common\services\abiturientController\bachelor;

use common\components\AttachmentManager;
use common\components\PageRelationManager;
use common\components\RegulationManager;
use common\models\Attachment;
use common\models\attachment\attachmentCollection\ApplicationAttachmentCollection;
use common\models\AttachmentType;
use common\models\errors\RecordNotFound;
use common\models\relation_presenters\comparison\EntitiesComparator;
use common\models\relation_presenters\comparison\results\ComparisonResult;
use common\models\User;
use common\models\UserRegulation;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\repositories\FileRepository;
use common\modules\abiturient\models\repositories\RegulationRepository;
use common\modules\abiturient\models\services\NextStepService;
use common\services\abiturientController\BaseService;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;




class BachelorService extends BaseService
{
    





    public function checkAccessibility(User $currentUser, int $id): void
    {
        BachelorApplication::checkAccessibility($currentUser, $id);
    }

    




    public function getApplication(int $id): BachelorApplication
    {
        $application = BachelorApplication::findOne($id);
        if (!$application) {
            throw new RecordNotFound('Заявление не найдено');
        }
        
        $application->archiveAdmissionCampaignHandler->handle();

        return $application;
    }

    public function getBachelorSpeciality(int $bachelor_spec_id): BachelorSpeciality
    {
        $bachelor_spec = BachelorSpeciality::findOne($bachelor_spec_id);
        if (!$bachelor_spec) {
            throw new RecordNotFound('Направление не найдено');
        }

        return $bachelor_spec;
    }

    





    public function getApplicationComparison(User $currentUser, BachelorApplication $application): ?ComparisonResult
    {
        $applicationComparison = null;
        $actualApplication = $this->getActualApplication($currentUser, $application);

        if (
            $actualApplication &&
            $actualApplication->id != $application->id &&
            $application->draft_status != IDraftable::DRAFT_STATUS_APPROVED
        ) {
            $applicationComparison = EntitiesComparator::compare($actualApplication, $application);
        };

        return $applicationComparison;
    }

    




    public function checkIfAbiturientQuestionaryFilled(BachelorApplication $application): bool
    {
        return !($application->abiturientQuestionary == null || $application->abiturientQuestionary->isNotFilled());
    }

    






    public function checkAttachmentFiles(
        BachelorApplication $application,
        bool                $canEdit,
        ?string             $attachmentTypeRelatedEntity = null
    ): array {
        $attachmentErrors = [];
        $isAttachmentsAdded = false;

        $required_attachments_check = Attachment::getNotFilledRequiredAttachmentTypeIds(
            $application->getEduAttachments()->with(['attachmentType'])->all(),
            AttachmentType::GetRequiredCommonAttachmentTypeIds($attachmentTypeRelatedEntity, ArrayHelper::getValue($application, 'type.campaign.referenceType.reference_uid'))
        );
        if ($required_attachments_check && $canEdit) {
            $types = AttachmentType::find()->where(['in', 'id', $required_attachments_check])->select(['id', 'name'])->asArray()->all();
            $attachmentErrors = ArrayHelper::map($types, 'id', 'name');
        } else {
            $isAttachmentsAdded = true;
        }

        return [
            'isAttachmentsAdded' => $isAttachmentsAdded,
            'attachmentErrors' => $attachmentErrors,
        ];
    }

    




    public function processErrorMessageProcessingSavingAttachment(
        Throwable $th,
        ?string $path = null
    ): void {
        Yii::$app->session->setFlash('alert', [
            'body' => Yii::t('abiturient/errors', 'Сообщение поступающему о ошибке сохранения формы скан-копий: `Возникла ошибка сохранения формы. Обратитесь к администратору.`'),
            'options' => ['class' => 'alert-danger']
        ]);

        Yii::error("Ошибка обработки формы сохранения скан-копий: {$th->getMessage()} в:" . PHP_EOL . $th->getTraceAsString(), $path);
    }

    





    private function getActualApplication(User $currentUser, BachelorApplication $application): ?BachelorApplication
    {
        $actualApplication = null;
        if ($application->draft_status != IDraftable::DRAFT_STATUS_APPROVED) {
            $actualApplication = DraftsManager::getActualApplication($currentUser, $application->type);
        }

        return $actualApplication;
    }

    






    protected function getRegulationsAndAttachments(
        BachelorApplication $application,
        string              $relatedEntityTypeAttachments,
        string              $relatedEntityTypeRegulations
    ): array {
        return [
            'attachments' => $this->getAttachmentList($application, $relatedEntityTypeAttachments),
            'regulations' => $this->getRegulationList($application, $relatedEntityTypeRegulations),
        ];
    }

    










    protected function postProcessingRegulationsAndAttachments(
        BachelorApplication $application,
        array               $attachments,
        array               $regulations
    ): array {
        if ($this->updateAttachments($application, $attachments, $regulations)) {
            return [
                'hasChanges' => true,
                'attachments' => $attachments,
                'regulations' => $regulations,
            ];
        }

        foreach ($regulations as $regulation) {
            

            if (
                $regulation->isNewRecord &&
                (!(int)$regulation->is_confirmed && (int)$regulation->regulation->confirm_required)
            ) {
                $regulation->addError('is_confirmed', Yii::t(
                    'abiturient/attachment-widget',
                    'Подсказка с ошибкой для поля "is_confirmed" на форме виджета сканов: `Необходимо подтвердить прочтение нормативного документа`'
                ));
            }
        }

        return [
            'hasChanges' => false,
            'attachments' => $attachments,
            'regulations' => $regulations,
        ];
    }

    





    protected function getAttachmentList(BachelorApplication $application, $relatedEntity = null)
    {
        if ($relatedEntity === null) {
            $relatedEntity = PageRelationManager::GetFullRelatedListForApplication();
        }

        return FileRepository::GetAttachmentCollectionsFromTypes($application, $relatedEntity);
    }

    





    private function getRegulationList(BachelorApplication $application, $relatedEntity)
    {
        $regulations = [];
        $existing_regulation = $application->getRegulations($relatedEntity)->all();
        $regulation_to_add = RegulationRepository::GetNotExistingRegulationsForEntity($relatedEntity, ArrayHelper::getColumn($existing_regulation, 'regulation_id'));
        foreach ($regulation_to_add as $regulation) {
            $userRegulation = new UserRegulation();
            $userRegulation->regulation_id = $regulation->id;
            $userRegulation->application_id = $application->id;
            $userRegulation->owner_id = $application->user->id;
            $regulations[] = $userRegulation;
        }
        $regulations = array_merge($regulations, $existing_regulation);

        foreach ($regulations as $regulation) {
            if ($regulation->regulation->attachment_type && $regulation->getAttachments()->exists()) {
                $regulationAttachment = new Attachment();
                $regulationAttachment->owner_id = $application->user_id;
                $regulationAttachment->attachment_type_id = $regulation->regulation->attachment_type;
                $regulation->setRawAttachment($regulationAttachment);
            }
        }

        ArrayHelper::multisort($regulations, 'regulation_id', SORT_ASC, SORT_NUMERIC);
        return $regulations;
    }

    








    private function updateAttachments(
        BachelorApplication $application,
        array               $attachments,
        array               $userRegulations,
        bool                $updateHistory = false,
        ?int                $appHistoryType = null
    ): bool {
        $has_changes = false;
        if (RegulationManager::handleRegulations($userRegulations, $this->request)) {
            $application->resetStatus();
            $has_changes = true;
        }
        $all_new_attachments = AttachmentManager::handleAttachmentUpload($attachments, $userRegulations);
        if ($all_new_attachments) {
            $application->resetStatus();
            $has_changes = true;
        }
        if ($updateHistory) {
            $relatedEntities = [];
            $entitiesToHistory = [];

            if ($all_new_attachments && !$appHistoryType) {
                
                

                $attachmentTypes = AttachmentType::find()
                    ->select(['id', 'related_entity'])
                    ->andWhere([
                        'id' => ArrayHelper::getColumn($all_new_attachments, 'attachment_type_id'),
                        'hidden' => false
                    ])
                    ->asArray()
                    ->all();
                $relatedEntities = ArrayHelper::map($attachmentTypes, 'id', 'related_entity');

                $entitiesToHistory = [
                    AttachmentType::RELATED_ENTITY_APPLICATION => ApplicationHistory::TYPE_SPECIALITY_CHANGED,
                    AttachmentType::RELATED_ENTITY_EDUCATION => ApplicationHistory::TYPE_EDUCATION_CHANGED,
                    AttachmentType::RELATED_ENTITY_EGE => ApplicationHistory::TYPE_EXAM_CHANGED,
                ];
            }
            foreach ($all_new_attachments as $attachment) {
                if (!empty($entitiesToHistory)) {
                    $appHistoryType = $entitiesToHistory[$relatedEntities[$attachment->attachment_type_id]] ?? null;
                }

                if (!is_null($appHistoryType)) {
                    $application->addApplicationHistory($appHistoryType);
                }
            }
        }

        return $has_changes;
    }

    





    protected function getNextStep(BachelorApplication $application, string $currentStep): string
    {
        $next_step_service = new NextStepService($application);
        if ($next_step_service->getUseNextStepForwarding()) {
            $next_step = $next_step_service->getNextStep($currentStep);
            if ($next_step) {
                return Url::to($next_step_service->getUrlByStep($next_step));
            }
        }

        return '';
    }
}
