<?php


namespace common\modules\abiturient\models\bachelor\changeHistory\rows;


class AbiturientJuxtapositionRow extends DefaultChangeHistoryRow
{
    public function getRowTitle(): string
    {
        return '<strong>Анкета.</strong> Сопоставление с физ. лицом';
    }

    public function getIconColor(): string
    {
        return 'info';
    }
}