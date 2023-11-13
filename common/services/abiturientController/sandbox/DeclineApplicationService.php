<?php

namespace common\services\abiturientController\sandbox;

use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\bachelor\ApplicationHistory;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow\models\ApplicationAcceptDeclineModel;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\services\abiturientController\BaseService;

class DeclineApplicationService extends BaseService
{
    







    public function decline(User $currentUser, BachelorApplication $application): BachelorApplication
    {
        $application->fullyUnblockApplication();
        $application->refresh();

        $application->load($this->request->post());
        $application->status = BachelorApplication::STATUS_NOT_APPROVED;

        $application->approver_id = null;
        $application->approved_at = null;

        $lastManagerId = $currentUser->id;
        $application->setLastManager($lastManagerId);
        if (!$application->save()) {
            throw new RecordNotValid($application);
        }
        $application->addModerateHistory($currentUser);
        ApplicationHistory::deleteAll(['application_id' => $application->id]);

        $this->writeDeclineToChangeHistory($application);

        $declinedApp = DraftsManager::createArchivePoint(
            $application,
            DraftsManager::REASON_DECLINED,
            IDraftable::DRAFT_STATUS_SENT
        );

        
        DraftsManager::clearOldModerations($declinedApp, $currentUser, DraftsManager::REASON_DECLINED);
        DraftsManager::clearOldSendings($declinedApp, $currentUser, DraftsManager::REASON_DECLINED);

        $declinedApp->type->toggleResubmitPermissions($declinedApp->user, true);

        return $declinedApp;
    }

    






    private function writeDeclineToChangeHistory(BachelorApplication $application): void
    {
        $change = new ApplicationAcceptDeclineModel();
        $change->application = $application;
        $change->application_action_status = ApplicationAcceptDeclineModel::APPLICATION_REJECT;
        $change->application_comment = $application->moderator_comment;

        $change->getChangeHistoryHandler()->getInsertHistoryAction()->proceed();
    }
}
