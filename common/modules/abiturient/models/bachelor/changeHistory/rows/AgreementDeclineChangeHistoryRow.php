<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows;

use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;

class AgreementDeclineChangeHistoryRow extends DefaultChangeHistoryRow
{
    public function getRowTitle(): string
    {
        $identifier = $this->getChangeHistoryEntityClass()->entity_identifier;
        
        if ($this->getChangeHistoryEntityClass() 
            && $this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_DELETE) {
            $str = '<strong>Заявление.</strong> Отзыв отказа от согласия на зачисление. ';
        } else {
            $str = '<strong>Заявление.</strong> Отзыв согласия на зачисление. ';
        }
        
        if(!empty($identifier)) {
            $str  .= $identifier;
        }
        return $str;
    }

    public function getContent(): array
    {
        return [];
    }

    public function getIcon(): string
    {
        if ($this->getChangeHistoryEntityClass() 
            && $this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_DELETE) {
            return 'fa fa-undo';
        }
        
        return 'fa fa-remove';
    }

    public function getIconColor(): string
    {
        if ($this->getChangeHistoryEntityClass() 
            && $this->getChangeHistoryEntityClass()->change_type === ChangeHistoryEntityClass::CHANGE_TYPE_DELETE) {
            return 'blue';
        }
        
        return 'red';
    }
}