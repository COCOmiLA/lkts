<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows\ApplicationAcceptDeclineRow;

use common\modules\abiturient\models\bachelor\changeHistory\rows\DefaultChangeHistoryRow;
use Yii;

class ApplicationAcceptRow extends DefaultChangeHistoryRow
{
    public function getRowTitle(): string
    {
        $titleText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись группы "Заявление" в которой произошли изменения виджета истории изменений: `Заявление`'
        );
        $actionText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись действия "заявление отправлено в приемную кампанию." в которой произошли изменения виджета истории изменений: `Заявление отправлено в приемную кампанию.`'
        );
        return "<strong>{$titleText}.</strong> {$actionText}";
    }

    public function getIcon(): string
    {
        return 'fa fa-paper-plane-o';
    }

    public function getIconColor(): string
    {
        return 'green';
    }
}
