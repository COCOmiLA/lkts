<?php

namespace common\services\abiturientController\sandbox;

use common\components\ChangeHistoryManager;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\errors\RecordNotValid;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\services\abiturientController\BaseService;
use Throwable;
use Yii;
use yii\helpers\ArrayHelper;

class BindApplicationService extends BaseService
{
    







    public function  bindUser(User $currentUser, int $id): bool
    {
        $userRefId = $this->request->post('user_ref_id');
        $userRef = StoredUserReferenceType::findOne($userRefId);
        $app = BachelorApplication::findOne($id);
        $user = $app->user;
        if (!$userRef || !$user) {
            return false;
        }

        $user->guid = $userRef->reference_id;
        $user->user_ref_id = $userRef->id ?? null;
        if (!$user->save()) {
            throw new RecordNotValid($user);
        }

        return $this->persistJuxtaposition($user, $currentUser);
    }

    




    public function  bindParent(): string
    {
        $questionary = AbiturientQuestionary::findOne($this->request->post('questionary_id'));
        if (!$questionary) {
            return '';
        }

        $parent = $questionary
            ->getParentData()
            ->andWhere(['id' => $this->request->post('parent_id')])
            ->one();
        if (!$parent) {
            return '';
        }

        $userRefId = ArrayHelper::getValue($this->request->post('parent'), "{$parent->id}.user_ref_id");
        $userRef = StoredUserReferenceType::findOne($userRefId);
        if (!$userRef) {
            return '';
        }

        $parent->code = $userRef->reference_id;
        $parent->parent_ref_id = $userRef->id;
        if (!$parent->save()) {
            throw new RecordNotValid($parent);
        }

        return $parent->personalData->absFullName;
    }

    










    private function persistJuxtaposition(User $user, User $currentUser): bool
    {
        $db = User::getDb();
        $transaction = $db->beginTransaction();
        try {
            foreach ($user->getAllAbiturientQuestionaries()->all() as $questionary) {
                $change = ChangeHistoryManager::persistChangeForEntity($currentUser, ChangeHistory::CHANGE_HISTORY_ABITURIENT_JUXTAPOSITION);
                $change->questionary_id = $questionary->id;

                if (!$change->save()) {
                    throw new RecordNotValid($change);
                }
                $class = ChangeHistoryManager::persistChangeHistoryEntity($questionary, ChangeHistoryEntityClass::CHANGE_TYPE_UPDATE);
                $class->setChangeHistory($change);
                if (!$class->save()) {
                    throw new RecordNotValid($class);
                }

                foreach (ChangeHistoryManager::persistChangeHistoryEntityInputs($class, $questionary, false) as $detail) {
                    $detail->setEntityClass($class);
                    if (!$detail->save()) {
                        throw new RecordNotValid($detail);
                    }
                }
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            Yii::error("Ошибка сохранения истории сопоставления ФЛ: {$e->getMessage()}");

            return false;
        }

        return true;
    }
}
