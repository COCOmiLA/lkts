<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows;

use Yii;

class ApplicationReturnRow extends DefaultChangeHistoryRow
{
    public function getRowTitle(): string
    {
        $titleText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись группы "Заявление" в которой произошли изменения виджета истории изменений: `Заявление`'
        );
        $actionText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись действия "отзыв документов из приёмной кампании." в которой произошли изменения виджета истории изменений: `Отзыв документов из приёмной кампании.`'
        );
        return "<strong>{$titleText}.</strong> {$actionText}";
    }

    public function getIcon(): string
    {
        return 'fa fa-ban';
    }

    public function getIconColor(): string
    {
        return 'red';
    }
}
