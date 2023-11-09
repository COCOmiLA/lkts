<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow;

use common\modules\abiturient\models\bachelor\changeHistory\rows\DefaultChangeHistoryRow;
use Yii;

class ApplicationDeclineRow extends DefaultChangeHistoryRow
{
    public function getRowTitle(): string
    {
        $titleText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись группы "Заявление" в которой произошли изменения виджета истории изменений: `Заявление`'
        );
        $actionText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись действия "отклонение заявления модератором." в которой произошли изменения виджета истории изменений: `Отклонение заявления модератором.`'
        );
        return "<strong>{$titleText}.</strong> {$actionText}";
    }

    public function getIcon(): string
    {
        return 'fa fa-remove';
    }

    public function getIconColor(): string
    {
        return 'red';
    }
}
