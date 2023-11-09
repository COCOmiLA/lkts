<?php

namespace common\components\changeHistoryHandler\actions;


use common\components\changeHistoryHandler\interfaces\ChangeHistoryActionInterface;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\models\errors\RecordNotValid;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;

class BaseAction implements ChangeHistoryActionInterface
{

    


    private $handler;

    


    private $isInsert;

    


    public function setIsInsert(bool $isInsert): void
    {
        $this->isInsert = $isInsert;
    }

    public function getIsInsert(): bool
    {
        return $this->isInsert;
    }

    public function __construct(ChangeHistoryHandlerInterface $handler, $isInsert = false)
    {
        $this->setHandler($handler);
        $this->setIsInsert($isInsert);
    }

    public function getHandler(): ChangeHistoryHandlerInterface
    {
        return $this->handler;
    }

    public function setHandler(ChangeHistoryHandlerInterface $handler): void
    {
        $this->handler = $handler;
    }

    public function proceed(): bool
    {
        $change = $this->handler->persistChange();
        $newChangeEntity = $this->handler->persistChangeHistoryEntity();
        $details = $this->persistChangeDetails($newChangeEntity);

        
        if ($details) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if (!$change->save()) {
                    throw new RecordNotValid($change);
                }
                $newChangeEntity->setChangeHistory($change);
                if (!$newChangeEntity->save()) {
                    throw new RecordNotValid($newChangeEntity);
                }
                foreach ($details as $detail) {
                    $detail->setEntityClass($newChangeEntity);
                    if (!$detail->save()) {
                        throw new RecordNotValid($detail);
                    }
                }

                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
        return true;
    }

    public function persistChangeDetails(ChangeHistoryEntityClass $changeEntity): array
    {
        return [];
    }
}