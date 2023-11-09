<?php

namespace common\components\changeHistoryHandler\actions;


use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClassInput;

class DeleteAction extends BaseAction
{
    public function persistChangeDetails(ChangeHistoryEntityClass $changeEntity): array
    {
        $details = [];
        $entity = $this->getHandler()->getEntity();
        $valueGetter = $changeEntity->getHistoryValueGetter($entity);
        foreach ($entity->getChangeLoggedAttributes() as $attr => $getter) {

            if (!is_string($attr)) {
                $attr = $getter;
            }

            $newChangeDetail = new ChangeHistoryEntityClassInput();

            $oldValue = $valueGetter->getValue($attr);

            if ($oldValue === null) {
                continue;
            }

            $newChangeDetail->setOldValue($oldValue);

            $newChangeDetail->input_name = $attr;

            $details[] = $newChangeDetail;
        }
        return $details;
    }
}