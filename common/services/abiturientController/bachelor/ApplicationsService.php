<?php

namespace common\services\abiturientController\bachelor;

use common\components\AttachmentManager;
use common\components\ChangeHistoryManager;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\services\abiturientController\bachelor\BachelorService;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;

class ApplicationsService extends BachelorService
{
    




    public function getApplications(User $currentUser): array
    {
        $currentUser->syncApplicationsAndQuestionaryWith1C();
        return $currentUser->getApplications()
            ->andWhere(['not', [BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_MODERATING]])
            ->all();
    }

    





    public function archiveApplications(User $currentUser, BachelorApplication $application): void
    {
        $this->changeHistoryApplicationReturnDocs($currentUser, $application);
        $application
            ->setArchiveInitiator($currentUser)
            ->setArchiveReason(DraftsManager::REASON_RETURN)
            ->archive();
    }

    







    public function markReturnApplication(User $currentUser, BachelorApplication $application): void
    {
        $application = DraftsManager::createApplicationDraftByOtherDraft($application, IDraftable::DRAFT_STATUS_SENT);

        if ($this->request->isPost) {
            AttachmentManager::handleAttachmentUpload([$application->getApplicationReturnAttachmentCollection()]);
        }

        $application->status = BachelorApplication::STATUS_WANTS_TO_RETURN_ALL;
        $application->sent_at = time();
        if (!$application->save()) {
            throw new RecordNotValid($application);
        }

        $this->changeHistoryApplicationReturnDocs($currentUser, $application);
        DraftsManager::clearOldSendings($application, $currentUser, DraftsManager::REASON_RETURN);
        DraftsManager::clearOldModerations($application, $currentUser, DraftsManager::REASON_RETURN);
    }

    





    public function returnApplication(BachelorApplication $application, bool $sandboxEnabled): string
    {
        if (!$sandboxEnabled && $this->request->isPost) {
            AttachmentManager::handleAttachmentUpload([$application->getApplicationReturnAttachmentCollection()]);
        }

        $errorMessage = $this->returnApplicationIn1C($application);
        if (!$errorMessage) {
            $db = BachelorApplication::getDb();
            $transaction = $db->beginTransaction();

            try {
                $application->markApplicationRemoved();

                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();

                $errorMessage = $e->getMessage();
                Yii::error("Ошибка отзыва заявления в ЛК: {$e->getMessage()} в:" . PHP_EOL . $e->getTraceAsString(), 'ApplicationsService.returnApplication');
            }
        }

        return $errorMessage;
    }

    







    private function changeHistoryApplicationReturnDocs(User $currentUser, BachelorApplication $application): void
    {
        $change = ChangeHistoryManager::persistChangeForEntity($currentUser, ChangeHistory::CHANGE_HISTORY_APPLICATION_RETURN_DOCS);
        $change->application_id = $application->id;
        if (!$change->save()) {
            throw new RecordNotValid($change);
        }
    }

    







    private function returnApplicationIn1C(BachelorApplication $application): string
    {
        if (!$application->isIn1C()) {
            return '';
        }

        $result = null;
        $errorMessage = '';

        $files_info = $application->getApplicationReturnFilesInfo();
        $attachment = array_pop($files_info); 
        $returnAllApplicationsData = [
            'AbiturientCode' => ArrayHelper::getValue($application, 'user.userRef.reference_id'),
            'IdPK' => ArrayHelper::getValue($application, 'type.campaign.referenceType.reference_id'),
            'Entrant' => $application->buildEntrantArray(),
            'ReturnDocumentsScan' => AttachmentManager::buildAttachmentArrayTo1C(...$attachment)
        ];
        try {
            $result = Yii::$app->soapClientAbit->load(
                'ReturnAllApplications',
                $returnAllApplicationsData
            );
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
            Yii::error("Ошибка отзыва заявления из 1С: {$e->getMessage()} в:" . PHP_EOL . $e->getTraceAsString(), 'ApplicationsService.returnApplicationIn1C');

            return $errorMessage;
        }

        if (
            !$errorMessage &&
            isset(
                $result->return,
                $result->return->UniversalResponse,
                $result->return->UniversalResponse->Complete
            ) &&
            $result->return->UniversalResponse->Complete == '0'
        ) {
            $log = [
                'data' => $returnAllApplicationsData,
                'result' => $result,
            ];
            $errorMessage = $result->return->UniversalResponse->Description . PHP_EOL . print_r($log, true);
        }

        if ($errorMessage) {
            Yii::error("Ошибка при выполнении метода ReturnAllApplications: {$errorMessage}", 'ApplicationsService.returnApplicationIn1C');
        }

        return $errorMessage;
    }

    





    public function markRejectEnrollment(User $current_user, BachelorSpeciality $bachelor_spec): BachelorApplication
    {
        $spciality_id = $bachelor_spec->speciality_id;
        $application = $bachelor_spec->application;

        if ($application->draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
            $application = DraftsManager::createApplicationDraftByOtherDraft($bachelor_spec->application, IDraftable::DRAFT_STATUS_SENT);
        }

        $bachelor_spec_to_reject = $application->getSpecialities()
            ->joinWith('speciality speciality', false)
            ->andWhere(['speciality.id' => $spciality_id])
            ->one();

        if ($this->request->isPost) {
            $collection = $bachelor_spec_to_reject->getEnrollmentRejectionAttachmentCollection();
            $collection->setIndex($bachelor_spec->id); 
            AttachmentManager::handleAttachmentUpload([$collection]);
        }

        $application->status = BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED;
        $application->sent_at = time();
        if (!$application->save()) {
            throw new RecordNotValid($application);
        }

        $this->changeHistoryApplicationReturnDocs($current_user, $application);
        DraftsManager::clearOldSendings($application, $current_user, DraftsManager::REASON_RETURN);
        DraftsManager::clearOldModerations($application, $current_user, DraftsManager::REASON_RETURN);

        return $application;
    }

    public function rejectEnrollment(User $current_user, BachelorSpeciality $bachelor_spec, bool $sandboxEnabled)
    {
        if (!$sandboxEnabled && $this->request->isPost) {
            AttachmentManager::handleAttachmentUpload([$bachelor_spec->getEnrollmentRejectionAttachmentCollection()]);
        }

        $application = $bachelor_spec->application;

        $errorMessage = $this->rejectEnrollmentIn1C($bachelor_spec);
        if (!$errorMessage) {
            $db = BachelorApplication::getDb();
            $transaction = $db->beginTransaction();

            try {
                $application->fullUpdateFrom1C();
                $application->unblockApplication();
                $application->status = BachelorApplication::STATUS_APPROVED;
                $application->save(true, ['status']);
                $application = DraftsManager::createArchivePoint(
                    $application,
                    DraftsManager::REASON_APPROVED,
                    IDraftable::DRAFT_STATUS_APPROVED
                );
                
                DraftsManager::clearOldSendings($application, $current_user, DraftsManager::REASON_APPROVED);
                DraftsManager::clearOldModerations($application, $current_user, DraftsManager::REASON_APPROVED);
                DraftsManager::removeOldApproved($application, $current_user, DraftsManager::REASON_APPROVED);
                $application->type->toggleResubmitPermissions($application->user, false);

                $transaction->commit();
            } catch (Throwable $e) {
                $transaction->rollBack();

                $errorMessage = $e->getMessage();
                Yii::error("Ошибка отказа от зачисления в ЛК: {$e->getMessage()} в:" . PHP_EOL . $e->getTraceAsString(), 'ApplicationsService.rejectEnrollment');
            }
        }

        return $errorMessage;
    }

    public function rejectEnrollmentIn1C(BachelorSpeciality $bachelor_spec)
    {
        $application = $bachelor_spec->application;

        if (!$application->isIn1C()) {
            return '';
        }

        $result = null;
        $errorMessage = '';

        $files_info = $bachelor_spec->getEnrollmentRejectionFilesInfo();
        if (empty($files_info)) {
            Yii::error("Ошибка отзыва заявления", 'ApplicationsService.returnApplicationIn1C');
            return Yii::t(
                'abiturient/bachelor/print-application-return-form/all', 
                'Сообщение об ошибке при отказе от зачисления: `Не найден файл отказа от зачисления`'
            );
        }

        $attachment = array_pop($files_info);
        $request = [
            'Entrant' => $application->buildEntrantArray(),
            'Speciality' => $bachelor_spec->buildSpecialityArrayForEnrollmentRejection(),
            'RejectEnrollmentScan' => AttachmentManager::buildAttachmentArrayTo1C(...$attachment)
        ];
        try {
            $result = Yii::$app->soapClientAbit->load(
                'RejectEnrollment',
                $request
            );
        } catch (Throwable $e) {
            $errorMessage = $e->getMessage();
            Yii::error("Ошибка отзыва заявления из 1С: {$e->getMessage()} в:" . PHP_EOL . $e->getTraceAsString(), 'ApplicationsService.returnApplicationIn1C');

            return $errorMessage;
        }

        $universal_response = $result->return->UniversalResponse ?? $result->return ?? null;
        $complete = $universal_response->Complete ?? 0;
        $description = $universal_response->Description ?? 'Неизвестная ошибка';
        
        if (!$errorMessage && !$complete) {
            $log = [
                'data' => $request,
                'result' => $result,
            ];
            $errorMessage = $description . PHP_EOL . print_r($log, true);
        }

        if ($errorMessage) {
            Yii::error("Ошибка при выполнении метода RejectEnrollment: {$errorMessage}", 'ApplicationsService.rejectEnrollmentIn1C');
        }

        return $errorMessage;
    }
}
