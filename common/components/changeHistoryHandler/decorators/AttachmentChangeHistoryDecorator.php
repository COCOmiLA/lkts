<?php
namespace common\components\changeHistoryHandler\decorators;

use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;

class AttachmentChangeHistoryDecorator extends BaseChangeHistoryDecorator
{
    public function persistChange(): ChangeHistory
    {
        $change = $this->decorated->persistChange();
        $change->change_type =  ChangeHistory::CHANGE_HISTORY_FILE;
        return $change;
    }
}