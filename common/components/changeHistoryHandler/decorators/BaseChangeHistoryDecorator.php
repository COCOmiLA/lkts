<?php
namespace common\components\changeHistoryHandler\decorators;

use common\components\changeHistoryHandler\interfaces\ChangeHistoryActionInterface;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;

class BaseChangeHistoryDecorator implements ChangeHistoryHandlerInterface
{
    


    protected $decorated;

    public function __construct(ChangeHistoryHandlerInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    public function persistChange(): ChangeHistory
    {
        return $this->decorated->persistChange();
    }

    public function persistChangeHistoryEntity(): ChangeHistoryEntityClass
    {
        return $this->decorated->persistChangeHistoryEntity();
    }

    public function getEntity(): ChangeLoggedModelInterface
    {
        return $this->decorated->getEntity();
    }

    public function setEntity(ChangeLoggedModelInterface $entity): void
    {
        $this->decorated->setEntity($entity);
    }

    public function getDeleteHistoryAction(): ChangeHistoryActionInterface
    {
        $action = $this->decorated->getDeleteHistoryAction();
        $action->setHandler($this);
        return $action;
    }

    public function getUpdateHistoryAction(): ChangeHistoryActionInterface
    {
        $action = $this->decorated->getUpdateHistoryAction();
        $action->setHandler($this);
        return $action;
    }

    public function getInsertHistoryAction(): ChangeHistoryActionInterface
    {
        $action = $this->decorated->getInsertHistoryAction();
        $action->setHandler($this);
        return $action;
    }

    public function getInitiator(): ?User
    {
        return $this->decorated->getInitiator();
    }

    public function setInitiator($user)
    {
        $this->decorated->setInitiator($user);
    }

    public function getActionType(): int
    {
        return $this->decorated->getActionType();
    }

    public function getEntityIdentifier(): ?string
    {
        return $this->decorated->getEntityIdentifier();
    }

    public function setEntityIdentifier(?string $entityIdentifier)
    {
        $this->decorated->setEntityIdentifier($entityIdentifier);
    }

    public function getDisabled(): bool
    {
        return $this->decorated->getDisabled();
    }

    public function setDisabled(bool $disabled)
    {
        $this->decorated->setDisabled($disabled);
    }
}