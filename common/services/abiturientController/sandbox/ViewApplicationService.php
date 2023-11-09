<?php

namespace common\services\abiturientController\sandbox;

use common\components\RegulationRelationManager;
use common\models\Attachment;
use common\models\errors\RecordNotValid;
use common\models\repositories\UserRegulationRepository;
use common\models\User;
use common\models\UserRegulation;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\interfaces\ApplicationInterface;
use common\modules\abiturient\models\interfaces\IDraftable;
use common\services\abiturientController\BaseService;
use yii\base\UserException;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ServerErrorHttpException;

class ViewApplicationService extends BaseService
{
    









    public function returnToModerate(User $currentUser, BachelorApplication $application, bool $remove_from_one_s): BachelorApplication
    {
        $status = true;
        $message = '';
        if ($remove_from_one_s) {
            [$status, $message] = $application->entirelyRemoveAppFromOneS();
        }
        if (!$status) {
            throw new ServerErrorHttpException("Не удалось вернуть заявление на модерацию по причине: " . $message);
        }
        $application->status = ApplicationInterface::STATUS_SENT_AFTER_NOT_APPROVED;
        $application->draft_status = IDraftable::DRAFT_STATUS_SENT;
        $application->sent_at = time();
        if (!$application->save()) {
            throw new RecordNotValid($application);
        }

        
        $application = DraftsManager::createArchivePoint(
            $application,
            DraftsManager::REASON_SENT,
            IDraftable::DRAFT_STATUS_SENT
        );

        DraftsManager::clearOldModerations($application, $currentUser, DraftsManager::REASON_SENT);
        DraftsManager::clearOldSendings($application, $currentUser, DraftsManager::REASON_SENT);
        if ($remove_from_one_s) {
            
            DraftsManager::removeOldApproved($application, $currentUser, DraftsManager::REASON_APPROVED);
        }

        $application->type->toggleResubmitPermissions($application->user, true);

        return $application;
    }

    










    public function getAllModelForView(?int $applicationId): array
    {
        $application = BachelorApplication::findOne($applicationId);
        $questionary = $application->abiturientQuestionary;

        return [
            'application' => $application,
            'questionary' => $questionary,
            'regulations' => $this->getRegulations($application, $questionary),
            'moderatingAppId' => $this->getModeratingApplicationId($application),
            'individualAchievements' => new ActiveDataProvider([
                'query' => $application->getIndividualAchievements()
            ]),
        ];
    }

    






    public function getArchiveApplicationForView(?int $userId, ?int $applicationId, Controller $controller): array
    {
        $tnBachelorApplication = BachelorApplication::tableName();
        $applicationTypeQueryFilter = BachelorApplication::find()
            ->select('type_id')
            ->andWhere([
                "{$tnBachelorApplication}.id" => $applicationId,
                "{$tnBachelorApplication}.user_id" => $userId,
            ]);
        $applications = BachelorApplication::find()
            ->andWhere(["{$tnBachelorApplication}.user_id" => $userId])
            ->andWhere(["{$tnBachelorApplication}.parent_draft_id" => null])
            ->andWhere(['IN', "{$tnBachelorApplication}.type_id", $applicationTypeQueryFilter])
            ->orderBy(["{$tnBachelorApplication}.created_at" => SORT_ASC])
            ->all();

        return BachelorApplication::getApplicationArchiveNode($applications, $applicationId, $controller);
    }

    




    private function getModeratingApplicationId(BachelorApplication $application): ?int
    {
        $moderatingAppId = null;
        $moderatingApp = DraftsManager::getApplicationDraftByOtherDraft(
            $application,
            $application->getDraftStatusToModerate()
        );
        if ($moderatingApp) {
            $moderatingAppId = $moderatingApp->id;
        }

        return $moderatingAppId;
    }

    





    private function getRegulations(BachelorApplication $application, AbiturientQuestionary $questionary): array
    {
        $regulations = UserRegulationRepository::GetAllUserRegulationsByRelatedEntity(
            $questionary,
            array_keys(RegulationRelationManager::GetRelatedList()),
            $application
        );

        foreach ($regulations as $regulation) {
            if ($regulation->regulation->attachment_type !== null && $regulation->getAttachments()->exists()) {
                $newAttachment = new Attachment();
                $newAttachment->owner_id = $application->user_id;
                $newAttachment->attachment_type_id = $regulation->regulation->attachment_type;
                $regulation->setRawAttachment($newAttachment);
            }
        }

        return $regulations;
    }
}
