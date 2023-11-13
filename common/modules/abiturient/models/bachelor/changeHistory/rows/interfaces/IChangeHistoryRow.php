<?php
namespace common\modules\abiturient\models\bachelor\changeHistory\rows\interfaces;


use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;

interface IChangeHistoryRow
{
    public function getChangeHistory(): ChangeHistory;

    public function setChangeHistory(ChangeHistory $change);

    public function getRowTitle(): string;

    public function getIcon(): string;

    public function getIconColor(): string;

    


    public function getContent(): array;

    public function getInitiator(): string;
}