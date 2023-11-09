<?php

namespace common\services\abiturientController\bachelor;

use common\components\PageRelationManager;
use common\models\Attachment;
use common\models\attachment\attachmentCollection\AttachedEntityAttachmentCollection;
use common\models\AttachmentType;
use common\models\interfaces\IHaveDocumentCheckStatus;
use common\models\repositories\UserRegulationRepository;
use common\models\User;
use common\models\UserRegulation;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\repositories\FileRepository;
use common\services\abiturientController\bachelor\BachelorService;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

class LoadScansService extends BachelorService
{
    






    public function checkAttachmentFiles(
        BachelorApplication $application,
        bool                $canEdit,
        ?string             $attachmentTypeRelatedEntity = null
    ): array {
        $attachmentErrors = [];
        $isAttachmentsAdded = true;

        $existingAttachments = $application->getAllAttachmentsWithoutRegulations()->all();
        $required_attachments_check = Attachment::getNotFilledRequiredAttachmentTypeIds($existingAttachments, AttachmentType::GetRequiredCommonAttachmentTypeIds(null, ArrayHelper::getValue($application, 'type.campaign.referenceType.reference_uid')));
        if (
            $required_attachments_check &&
            !$application->isRequiredCommonFilesAttached() &&
            $canEdit
        ) {
            $types = AttachmentType::find()
                ->where(['in', 'id', $required_attachments_check])
                ->select(['id', 'name'])
                ->all();

            $attachmentErrors = ArrayHelper::map($types, 'id', 'name');
            $isAttachmentsAdded = false;
        }

        return [
            'isAttachmentsAdded' => $isAttachmentsAdded,
            'attachmentErrors' => $attachmentErrors,
        ];
    }

    




    public function getAllRegulationsAndAttachments(BachelorApplication $application): array
    {
        return [
            'attachments' => $this->getAllAttachmentsList($application),
            'regulations' => $this->getAllRegulationList($application),
        ];
    }

    










    public function postProcessingRegulationsAndAttachments(
        BachelorApplication $application,
        array               $attachments,
        array               $regulations
    ): array {
        if ($application->canEdit()) {
            $allAttachments = [];
            foreach ($attachments as $attachmentsArray) {
                $allAttachments = array_merge($allAttachments, $attachmentsArray);
            }
            $return = parent::postProcessingRegulationsAndAttachments($application, $allAttachments, $regulations);
            $return['attachments'] = $attachments;
            return $return;
        }

        return [
            'hasChanges' => false,
            'attachments' => $attachments,
            'regulations' => $regulations,
        ];
    }

    





    public function getNextStep(BachelorApplication $application, string $currentStep = 'load-scans'): string
    {
        return parent::getNextStep($application, $currentStep);
    }

    








    public function deleteAttachedFile(User $currentUser, int $id): bool
    {
        $attachment = Attachment::findOne($id);
        if (!$attachment) {
            
            throw new NotFoundHttpException('Не удалось найти файл по ID');
        }
        if (!$attachment->checkAccess($currentUser)) {
            throw new ForbiddenHttpException('У вас нет прав удалять этот файл');
        }
        if (!$attachment->isCommon()) {
            $entity = $attachment->entity;
            if ($entity instanceof IHaveDocumentCheckStatus && !$entity->read_only) {
                $entity->setDocumentCheckStatusNotVerified();
                $entity->save(['document_check_status_ref_id']);
            }
        }

        
        $attachment->safeDelete($currentUser);

        return true;
    }

    




    private function getAllAttachmentsList(BachelorApplication $application)
    {
        $attachments = [];
        $attachments[Yii::t(
            'abiturient/bachelor/load-scans/all',
            'Заголовок блока сканов для формы "анкеты": `Скан-копии анкеты`'
        )] = $this->getQuestionaryAndPassportAttachmentList($application);

        $attachments[Yii::t(
            'abiturient/bachelor/load-scans/all',
            'Заголовок блока сканов для формы "документов об образовании": `Скан-копии раздела документов об образовании`'
        )] = [
            ...ArrayHelper::getColumn($application->educations ?? [], 'attachmentCollection'),
            ...$this->getAttachmentList($application, PageRelationManager::RELATED_ENTITY_EDUCATION)
        ];

        $attachments[Yii::t(
            'abiturient/bachelor/load-scans/all',
            'Заголовок блока сканов для формы "направлений подготовки": `Скан-копии раздела направлений подготовки`'
        )] = array_merge($this->getAttachmentList($application, PageRelationManager::RELATED_ENTITY_APPLICATION), [$application->getApplicationReturnAttachmentCollection()]);

        $attachments[Yii::t(
            'abiturient/bachelor/load-scans/all',
            'Заголовок блока сканов для формы "результатов ЕГЭ": `Скан-копии раздела вступительных испытаний`'
        )] = $this->getAttachmentList($application, PageRelationManager::RELATED_ENTITY_EGE);

        $attachments[Yii::t(
            'abiturient/bachelor/load-scans/all',
            'Заголовок блока сканов для формы "льгот": `Скан-копии льгот`'
        )] = ArrayHelper::getColumn($application->preferences ?? [], 'attachmentCollection');

        $attachments[Yii::t(
            'abiturient/bachelor/load-scans/all',
            'Заголовок блока сканов для формы "целевых направлений": `Скан-копии целевых направлений`'
        )] = ArrayHelper::getColumn($application->bachelorTargetReceptions ?? [], 'attachmentCollection');

        $attachments[Yii::t(
            'abiturient/bachelor/load-scans/all',
            'Заголовок блока сканов для формы "индивидуальных достижений": `Скан-копии индивидуальных достижений`'
        )] = ArrayHelper::getColumn($application->individualAchievements ?? [], 'attachmentCollection');

        return $attachments;
    }

    




    private function getAllRegulationList(BachelorApplication $application)
    {
        $regulations = UserRegulationRepository::GetUserRegulationsWithFilesByApplicationAndRelatedEntity($application);
        foreach ($regulations as $regulation) {
            if ($regulation->regulation->attachment_type !== null && $regulation->getAttachments()->exists()) {
                $attachmentToAdd = new Attachment();
                $attachmentToAdd->owner_id = $application->user_id;
                $attachmentToAdd->attachment_type_id = $regulation->regulation->attachment_type;
                $regulation->setRawAttachment($attachmentToAdd);
            }
        }

        return $regulations;
    }

    




    private function getQuestionaryAndPassportAttachmentList(BachelorApplication $application): array
    {
        $questionary = $application->abiturientQuestionary;

        return array_merge(
            FileRepository::GetQuestionaryCollectionsFromTypes($questionary, [
                PageRelationManager::RELATED_ENTITY_REGISTRATION,
                PageRelationManager::RELATED_ENTITY_QUESTIONARY
            ]),
            ArrayHelper::getColumn($questionary->passportData ?? [], 'attachmentCollection')
        );
    }
}
