<?php
namespace common\components\changeHistoryHandler\actions;



use common\components\ChangeHistoryManager;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;

class SaveAction extends BaseAction
{
    public function persistChangeDetails(ChangeHistoryEntityClass $changeEntity): array
    {
        return ChangeHistoryManager::persistChangeHistoryEntityInputs($changeEntity, $this->getHandler()->getEntity(), !$this->getIsInsert());
    }
}