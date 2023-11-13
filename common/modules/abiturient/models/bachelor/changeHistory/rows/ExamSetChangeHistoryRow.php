<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows;

use Yii;

class ExamSetChangeHistoryRow extends DefaultChangeHistoryRow
{
    public function getRowTitle(): string
    {
        $titleText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись группы "Заявление" в которой произошли изменения виджета истории изменений: `Заявление`'
        );
        $actionText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись действия "подтверждение списка вступительных испытаний." в которой произошли изменения виджета истории изменений: `Подтверждение списка вступительных испытаний.`'
        );
        return "<strong>{$titleText}.</strong> {$actionText}";
    }

    public function getContent(): array
    {
        return [];
    }

    public function getIcon(): string
    {
        return 'fa fa-check';
    }

    public function getIconColor(): string
    {
        return 'info';
    }
}
