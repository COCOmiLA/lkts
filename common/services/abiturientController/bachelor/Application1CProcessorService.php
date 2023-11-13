<?php

namespace common\services\abiturientController\bachelor;

use common\components\ChangeHistoryManager;
use common\components\configurationManager;
use common\components\notifier\notifier;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\ApplicationType;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\services\abiturientController\bachelor\BachelorService;
use Throwable;
use Yii;
use yii\helpers\Url;
use yii\web\Request;

class Application1CProcessorService extends BachelorService
{
    
    protected notifier $notifier;

    



    public function __construct(
        Request              $request,
        notifier             $notifier,
        configurationManager $configurationManager
    )
    {
        $this->request = $request;
        $this->notifier = $notifier;
        $this->configurationManager = $configurationManager;
    }

    




    public function updateApplication(BachelorApplication $application): BachelorApplication
    {
        $actual_app = DraftsManager::getActualApplication($application->user, $application->type, true);
        if ($actual_app && $application->draft_status == IDraftable::DRAFT_STATUS_APPROVED) {
            $application = $actual_app;
        }

        if (
            !$actual_app ||
            $application->id == $actual_app->id
        ) {
            return $application;
        }

        $application->fullUpdateFrom1C();

        return DraftsManager::createArchivePoint(
            $application,
            DraftsManager::REASON_UPDATED_FROM_1C,
            $application->draft_status
        );
    }

    





    public function createApplicationCopy(User $currentUser, BachelorApplication $application): array
    {
        $new_status = IDraftable::DRAFT_STATUS_CREATED;
        if ($application->isArchive()) {
            return [
                'url' => Url::to(['/abiturient/applications']),
                'error_alert' => Yii::t('abiturient/errors', 'Сообщение поступающему при работе с архивным заявлением: `Вы работаете с неактуальной версией заявления`'),
            ];
        }
        if (!$application->type->checkResubmitPermission($application->user) && $application->hasApprovedApplication()) {
            return [
                'url' => Url::to(['/abiturient/applications']),
                'error_alert' => Yii::t('abiturient/errors', 'Текст ошибки при повторной подаче заявления: `В данную приёмную капанию запрещена подача заявлений после одобрения модератором, для повторной подачи заявления необходимо обратиться в приёмную кампанию.`'),
            ];
        }

        if (!$application->canCreateDraft()) {
            return [
                'url' => '',
                'error_alert' => Yii::$app->configurationManager->getText('text_on_disable_creating_draft_if_exist_sent_application'),
            ];
        }

        if (!$currentUser->canMakeStep('make-application', $application)) {
            return [
                'url' => '',
                'error_alert' => Yii::t(
                    'abiturient/header',
                    'Текст алерта о необходимости заполнения анкеты на панели навигации ЛК: `Для создания заявления необходимо заполнить анкету`'
                ),
            ];
        }
        gc_disable();
        $db = BachelorApplication::getDb();
        $transaction = $db->beginTransaction();
        try {
            $old_app = DraftsManager::getApplicationDraft($application->user, $application->type, $new_status);
            $new_app = DraftsManager::createApplicationDraftByOtherDraft($application, $new_status);
            if ($old_app) {
                $old_app
                    ->setArchiveInitiator($currentUser)
                    ->archive();
            }
            $new_app->unblockApplication();

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        if ($application->draft_status == IDraftable::DRAFT_STATUS_APPROVED && $application->user) {
            
            $application->user->updateUserRefDataVersion();
        }

        return [
            'url' => Url::to(['bachelor/application', 'id' => $new_app->id]),
            'error_alert' => '',
        ];
    }

    




    public function getApplicationTypeFromPost(User $currentUser): ?ApplicationType
    {
        $typeId = $this->request->post('application_type');
        $existingApplication = BachelorApplication::find()
            ->where([
                BachelorApplication::tableName() . '.type_id' => (int)$typeId,
                BachelorApplication::tableName() . '.user_id' => $currentUser->id,
                BachelorApplication::tableName() . '.archive' => false
            ])
            ->andWhere(['not', [BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_MODERATING]])
            ->one();

        if ($existingApplication) {
            return null;
        }

        return ApplicationType::findOne([
            'archive' => false,
            'id' => (int)$typeId,
        ]);
    }

    





    public function createBachelorApplication(User $currentUser, ApplicationType $applicationType): ?BachelorApplication
    {
        $bachelorApplication = new BachelorApplication();
        $bachelorApplication->user_id = $currentUser->id;
        $bachelorApplication->type_id = $applicationType->id;
        if (!$bachelorApplication->save()) {
            throw new RecordNotValid($bachelorApplication);
        }

        $bachelorApplication->addApplicationHistory(ApplicationHistory::TYPE_QUESTIONARY_CHANGED);
        if (
            $currentUser->abiturientQuestionary != null &&
            $currentUser->abiturientQuestionary->addressData &&
            $currentUser->abiturientQuestionary->addressData->not_found
        ) {
            $bachelorApplication->addApplicationHistory(ApplicationHistory::TYPE_NOT_KLADR);
        }

        return $bachelorApplication;
    }

    





    public function sendApplicationTo1C(User $currentUser, BachelorApplication $application): array
    {
        $is_first_attempt = $application->isFirstAttemptSendApp();
        $hasError = false;
        if ($application->user_id != $currentUser->id) {
            return [];
        }
        if (!$this->configurationManager->sandboxEnabled) {
            $application->getNonSandboxSendHandler()->send();
        } else {
            [
                'hasError' => $hasError,
                'application' => $application
            ] = $this->sendApplicationToSandbox($currentUser, $application);
        }

        if ($hasError) {
            return [];
        }
        $application->notifyAboutSendApplicationToCommission($is_first_attempt);

        return [
            'category' => 'abiturient',
            'event' => 'application_apply',
            'data' => [
                'public_identity' => $currentUser->getPublicIdentity(),
                'user_id' => $currentUser->getId(),
                'campaign' => $application->type->campaignName,
            ]
        ];
    }

    





    private function sendApplicationToSandbox(User $currentUser, BachelorApplication $application): array
    {
        $hasError = false;
        $db = BachelorApplication::getDb();
        $transaction = $db->beginTransaction();
        try {
            
            $oldStatus = $application->status;
            $new_status = $this->applicationStatusSwitcher($oldStatus, $application);

            $application->moderator_comment = null;
            $application->approver_id = null;
            $application->approved_at = null;
            $application->status = $new_status;
            $application->sent_at = time();
            if (!$application->save()) {
                throw new RecordNotValid($application);
            }
            $change = ChangeHistoryManager::persistChangeForEntity($currentUser, ChangeHistory::CHANGE_HISTORY_APPLICATION_MODERATE);
            $change->application_id = $application->id;
            if (!$change->save()) {
                throw new RecordNotValid($change);
            }

            
            $application = DraftsManager::createArchivePoint(
                $application,
                DraftsManager::REASON_SENT,
                IDraftable::DRAFT_STATUS_SENT
            );

            DraftsManager::clearOldSendings($application, $currentUser, DraftsManager::REASON_SENT);
            DraftsManager::clearOldModerations($application, $currentUser, DraftsManager::REASON_SENT);

            $this->notifier->notifyAboutSendApplication($currentUser->id);

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();

            $hasError = true;
        }

        return ['hasError' => $hasError, 'application' => $application];
    }

    





    private function applicationStatusSwitcher(int $oldStatus, BachelorApplication $application): int
    {
        switch ($oldStatus) {
            case BachelorApplication::STATUS_CREATED:
                
                
                

                $new_status = BachelorApplication::STATUS_SENT;
                $actual_app = DraftsManager::getActualApplication($application->user, $application->type);
                if ($actual_app) {
                    return BachelorApplication::STATUS_SENT_AFTER_APPROVED;
                }
                
                $previous_sent_draft = $application->getParentDraft();
                if (!$previous_sent_draft) {
                    return $new_status;
                }

                if (in_array($previous_sent_draft->status, [ApplicationInterface::STATUS_REJECTED_BY1C, ApplicationInterface::STATUS_NOT_APPROVED])) {
                    return BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED;
                }

                if (in_array($previous_sent_draft->status, [ApplicationInterface::STATUS_SENT_AFTER_APPROVED, ApplicationInterface::STATUS_SENT_AFTER_NOT_APPROVED])) {
                    return $previous_sent_draft->status;
                }

                return $new_status;

            case BachelorApplication::STATUS_APPROVED:
                return BachelorApplication::STATUS_SENT_AFTER_APPROVED;

            case BachelorApplication::STATUS_NOT_APPROVED:
            case BachelorApplication::STATUS_REJECTED_BY1C:
                return BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED;

            case BachelorApplication::STATUS_SENT_AFTER_APPROVED:
            case BachelorApplication::STATUS_SENT_AFTER_NOT_APPROVED:
                return $oldStatus;

            case BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED:
                return BachelorApplication::STATUS_ENROLLMENT_REJECTION_REQUESTED;

            default:
                return BachelorApplication::STATUS_SENT;
        }
    }
}
