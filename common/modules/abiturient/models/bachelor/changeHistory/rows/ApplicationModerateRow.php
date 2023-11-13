<?php

namespace common\modules\abiturient\models\bachelor\changeHistory\rows;

use Yii;

class ApplicationModerateRow extends DefaultChangeHistoryRow
{
    public function getRowTitle(): string
    {
        $titleText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись группы "Заявление" в которой произошли изменения виджета истории изменений: `Заявление`'
        );
        $actionText = Yii::t(
            'abiturient/change-history-widget',
            'Подпись действия "отправка заявления на проверку модератором." в которой произошли изменения виджета истории изменений: `Отправка заявления на проверку модератором.`'
        );
        return "<strong>{$titleText}.</strong> {$actionText}";
    }

    public function getContent(): array
    {
        return [];
    }

    public function getIcon(): string
    {
        return 'fa fa-briefcase';
    }

    public function getIconColor(): string
    {
        return 'info';
    }
}
