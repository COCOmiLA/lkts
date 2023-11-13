<?php
namespace common\components\changeHistoryHandler\decorators;

use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow\models\ApplicationAcceptDeclineModel;





class ApplicationAcceptDeclineChangeHistoryDecorator extends BaseChangeHistoryDecorator
{

    public function persistChange(): ChangeHistory
    {
        $type = null;
        $en = $this->getEntity();
        if($en instanceof ApplicationAcceptDeclineModel) {
            if($en->application_action_status === ApplicationAcceptDeclineModel::APPLICATION_ACCEPTED) {
                $type = ChangeHistory::CHANGE_HISTORY_APPLICATION_SENT;
            } else {
                $type = ChangeHistory::CHANGE_HISTORY_APPLICATION_REJECT;
            }
        }

        $change = $this->decorated->persistChange();
        $change->change_type = $type;
        return $change;
    }
}