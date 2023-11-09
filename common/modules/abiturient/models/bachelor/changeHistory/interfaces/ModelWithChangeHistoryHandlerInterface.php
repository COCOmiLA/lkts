<?php


namespace common\modules\abiturient\models\bachelor\changeHistory\interfaces;


use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;






interface ModelWithChangeHistoryHandlerInterface
{
    public function getChangeHistoryHandler(): ?ChangeHistoryHandlerInterface;

    public function setChangeHistoryHandler(ChangeHistoryHandlerInterface $handler);
}