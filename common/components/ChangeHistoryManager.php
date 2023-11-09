<?php


namespace common\components;


use common\models\EntrantManager;
use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClassInput;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use yii\base\UserException;

class ChangeHistoryManager
{
    public static function persistChangeBase($type = ChangeHistory::CHANGE_HISTORY_TYPE_DEFAULT): ChangeHistory
    {
        $newChange = new ChangeHistory();
        $newChange->change_type = $type;
        return $newChange;
    }

    








    public static function persistChangeForEntity(User $initiator, int $type = ChangeHistory::CHANGE_HISTORY_TYPE_DEFAULT): ChangeHistory
    {
        $newChange = self::persistChangeBase($type);
        $newChange->initiator_id = $initiator->getId();

        return $newChange;
    }

    








    public static function persistChangeForEntityByManager(EntrantManager $initiator, $type = ChangeHistory::CHANGE_HISTORY_TYPE_DEFAULT): ChangeHistory
    {
        $newChange = self::persistChangeBase($type);
        $newChange->entrant_manager_id = $initiator->id;

        return $newChange;
    }

    






    public static function persistChangeHistoryEntity(ChangeLoggedModelInterface $entity, $type): ChangeHistoryEntityClass
    {
        $newChangeClass = new ChangeHistoryEntityClass();
        $newChangeClass->entity_id = $entity->getPrimaryKey();
        $newChangeClass->entity_classifier_id = $entity->getClassTypeForChangeHistory();
        if ($entity->getEntityIdentifier() !== null) {
            $newChangeClass->entity_identifier = $entity->getEntityIdentifier();
        }
        $newChangeClass->change_type = $type;
        return $newChangeClass;
    }

    










    public static function persistChangeHistoryEntityInputs(ChangeHistoryEntityClass $class, ChangeLoggedModelInterface $entity, bool $processOldValue = false)
    {
        $details = [];
        $valueGetter = $class->getHistoryValueGetter($entity);

        foreach ($entity->getChangeLoggedAttributes() as $attr => $getter) {

            if (!is_string($attr)) {
                $attr = $getter;
            }
            $newChangeDetail = new ChangeHistoryEntityClassInput();
            $newChangeDetail->setEntityClass($class);

            $value = $valueGetter->getValue($attr);

            $newChangeDetail->setValue($value);
            $newChangeDetail->input_name = $attr;

            if ($processOldValue) {
                $oldClass = $entity->getOldClass();
                $oldValueGetter = $class->getHistoryValueGetter($oldClass);
                $oldValue = $oldValueGetter->getValue($attr);
                if ($oldValue == $value) {
                    continue;
                }
                $newChangeDetail->setOldValue($oldValue);
            }
            if ($value === null && $newChangeDetail->old_value === null) {
                continue;
            }

            if (!$newChangeDetail->save()) {
                throw new UserException("Невозможно сохранить информацию о деталях изменения.\n\n" . print_r($newChangeDetail->errors, true));
            }

            $details[] = $newChangeDetail;
        }
        return $details;
    }
}