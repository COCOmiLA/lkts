<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows;

use Yii;

class AgreementChangeHistoryRow extends DefaultChangeHistoryRow
{
    public function getRowTitle(): string
    {
        $identifier = $this->getChangeHistoryEntityClass()->entity_identifier;
        $titleText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись группы "Заявление" в которой произошли изменения виджета истории изменений: `Заявление`'
        );
        $actionText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись действия "прикрепление согласия на зачисление" в которой произошли изменения виджета истории изменений: `Прикрепление согласия на зачисление`'
        );
        $str = "<strong>{$titleText}.</strong> . {$actionText}";
        if (!empty($identifier)) {
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
        return 'fa fa-plus';
    }

    public function getIconColor(): string
    {
        return 'green';
    }
}
