<?php


namespace common\components\changeHistoryHandler\interfaces;


use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeDetailInterface;

interface ChangeHistoryActionInterface
{
    public function proceed(): bool;

    



    public function persistChangeDetails(ChangeHistoryEntityClass $changeEntity): array;

    public function getHandler(): ChangeHistoryHandlerInterface;

    public function setHandler(ChangeHistoryHandlerInterface $handler): void;
}