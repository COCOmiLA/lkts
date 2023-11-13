<?php

namespace common\components\ApplicationSendHandler\NonSandboxSendHandler;

use common\components\ApplicationSendHandler\BaseApplicationSendHandler;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\modules\abiturient\models\parentData\ParentData;
use Yii;
use yii\helpers\ArrayHelper;






class NonSandboxSendHandler extends BaseApplicationSendHandler
{
    public function send(): bool
    {
        $application = $this->getApplication();

        $application->moderator_comment = null;
        $application->approver_id = null;
        $application->approved_at = null;

        $application->sent_at = time();
        $application->save(false, ['sent_at', 'moderator_comment', 'approver_id', 'approved_at']);

        if ($application->sendAllApplicationTo1C()) {
            $comment = $application->moderator_comment;
            Yii::$app->notifier->notifyAboutApplyApplication($application->user_id, $comment);

            
            $application = DraftsManager::createArchivePoint(
                $application,
                DraftsManager::REASON_APPROVED,
                IDraftable::DRAFT_STATUS_APPROVED
            );

            
            DraftsManager::clearOldSendings($application, Yii::$app->user->identity, DraftsManager::REASON_APPROVED);
            DraftsManager::clearOldModerations($application, Yii::$app->user->identity, DraftsManager::REASON_APPROVED);
            
            DraftsManager::removeOldApproved($application, Yii::$app->user->identity, DraftsManager::REASON_APPROVED);

            $application->type->toggleResubmitPermissions($application->user, false);

            return true;
        } else {
            DraftsManager::createArchivePoint(
                $application,
                DraftsManager::REASON_REJECTED_BY_1C,
                $application->draft_status
            );
            return false;
        }
    }
}
