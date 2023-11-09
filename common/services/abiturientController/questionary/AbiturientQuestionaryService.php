<?php

namespace common\services\abiturientController\questionary;

use common\models\Attachment;
use common\models\AttachmentType;
use common\models\relation_presenters\comparison\EntitiesComparator;
use common\models\relation_presenters\comparison\results\ComparisonResult;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\ActualAddressData;
use common\modules\abiturient\models\AddressData;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\PersonalData;
use common\services\abiturientController\BaseService;
use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;



class AbiturientQuestionaryService extends BaseService
{
    





    public function getQuestionaryById(User $currentUser, int $questionaryId): ?AbiturientQuestionary
    {
        return AbiturientQuestionary::findOne([
            'id' => $questionaryId,
            'user_id' => $currentUser->id,
        ]);
    }

    





    public function checkAccessibility(User $currentUser, int $id): void
    {
        AbiturientQuestionary::checkAccessibility($currentUser, $id);
    }

    






    public function getQuestionnaireDependentModels(AbiturientQuestionary $questionary, string $modelGetter, string $modelClass): ActiveRecord
    {
        $this->checkIsCorrectQuestionnaireDependentModels($modelClass);
        return $questionary->{$modelGetter} ?? new $modelClass(['questionary_id' => $questionary->id]);
    }

    





    public function checkAttachmentFiles(
        AbiturientQuestionary $questionary,
        bool                  $canEdit
    ): array {
        $attachmentErrors = [];
        $isAttachmentsAdded = false;

        $required_attachments_check = Attachment::getNotFilledRequiredAttachmentTypeIds(
            $questionary->getAttachments()
                ->with(['attachmentType'])
                ->all(),
            AttachmentType::GetRequiredCommonAttachmentTypeIds(AttachmentType::RELATED_ENTITY_QUESTIONARY)
        );
        if ($canEdit && $required_attachments_check && $questionary->status != AbiturientQuestionary::STATUS_CREATED) {
            $types = AttachmentType::find()
                ->where(['in', 'id', $required_attachments_check])
                ->select(['id', 'name'])
                ->asArray()
                ->all();

            $attachmentErrors = ArrayHelper::map($types, 'id', 'name');
        } else {
            $isAttachmentsAdded = true;
        }

        return [
            'isAttachmentsAdded' => $isAttachmentsAdded,
            'attachmentErrors' => $attachmentErrors,
        ];
    }

    





    public function getQuestionaryComparison(User $currentUser, AbiturientQuestionary $questionary): ?ComparisonResult
    {
        $questionaryComparison = null;

        $actualQuestionary =  $this->getActualQuestionary($currentUser);
        if (
            $actualQuestionary &&
            !$questionary->getIsNewRecord() &&
            $actualQuestionary->id != $questionary->id
        ) {
            $questionaryComparison = EntitiesComparator::compare($actualQuestionary, $questionary);
        }

        return $questionaryComparison;
    }

    






    private function checkIsCorrectQuestionnaireDependentModels(string $modelClass): bool
    {
        $correctQuestionnaireDependentModels = [
            AddressData::class,
            PersonalData::class,
            ActualAddressData::class,
        ];
        if (in_array(
            $modelClass,
            $correctQuestionnaireDependentModels
        )) {
            return true;
        }

        throw new UserException("Был передан класс не относящийся к «Анкете» ({$modelClass})");
    }

    




    private function getActualQuestionary(User $currentUser): ?AbiturientQuestionary
    {
        return DraftsManager::getActualQuestionary($currentUser);
    }
}
