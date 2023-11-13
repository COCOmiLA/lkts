<?php


namespace common\components\changeHistoryHandler;


use common\components\changeHistoryHandler\actions\DeleteAction;
use common\components\changeHistoryHandler\actions\EmptyAction;
use common\components\changeHistoryHandler\actions\SaveAction;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryActionInterface;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\components\ChangeHistoryManager;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerValidationException;
use common\components\EntrantModeratorManager\exceptions\EntrantManagerWrongClassException;
use common\components\IdentityManager\IdentityManager;
use common\models\EntrantManager;
use common\models\MasterSystemManager;
use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use yii\base\UserException;

class BaseChangeHistoryModelHandler implements ChangeHistoryHandlerInterface
{
    


    private $initiator;

    


    private $initiatorManager;


    


    protected $entity;

    


    protected $deleteAction;

    


    protected $saveAction;

    



    protected $actionType;

    


    protected $entityIdentifier;

    


    protected $disabled = false;


    



    public function __construct(ChangeLoggedModelInterface $entity)
    {
        $this->entity = $entity;
        $this->saveAction = new SaveAction($this);
        $this->deleteAction = new DeleteAction($this);
        $identity = IdentityManager::GetIdentityForHistory();

        if (is_null($identity)) {
            
            $this->setDisabled(true);
        } else {
            $this->setInitiator($identity);
        }
    }

    


    public function persistChange(): ChangeHistory
    {
        if (!is_null($this->initiator)) {
            return ChangeHistoryManager::persistChangeForEntity($this->getInitiator(), $this->entity->getEntityChangeType());
        } else if (!is_null($this->initiatorManager)) {
            return ChangeHistoryManager::persistChangeForEntityByManager($this->getInitiatorManager(), $this->entity->getEntityChangeType());
        }
        throw new UserException('Не удалось определить инициатора изменения');
    }

    




    public function persistChangeDetail(ChangeHistory $change): array
    {
        return [];
    }

    


    public function getInitiator(): ?User
    {
        return $this->initiator;
    }

    


    public function getInitiatorManager(): EntrantManager
    {
        return $this->initiatorManager;
    }

    




    public function setInitiator($user): void
    {
        if (($user instanceof User && $user->isModer()) || ($user instanceof MasterSystemManager)) {
            $this->initiatorManager = $user->getEntrantManagerEntity();
        }

        if ($user instanceof User) {
            $this->initiator = $user;
        }
    }

    


    public function getEntity(): ChangeLoggedModelInterface
    {
        return $this->entity;
    }

    


    public function setEntity(ChangeLoggedModelInterface $entity): void
    {
        $this->entity = $entity;
    }

    


    public function getActionType(): int
    {
        return $this->actionType;
    }

    



    public function getDeleteHistoryAction(): ChangeHistoryActionInterface
    {
        if ($this->disabled) {
            return new EmptyAction($this);
        }
        $this->actionType = ChangeHistoryEntityClass::CHANGE_TYPE_DELETE;
        return $this->deleteAction;
    }

    



    public function getInsertHistoryAction(): ChangeHistoryActionInterface
    {
        if ($this->disabled) {
            return new EmptyAction($this);
        }
        $this->actionType = ChangeHistoryEntityClass::CHANGE_TYPE_INSERT;
        $this->saveAction->setIsInsert(true);
        return $this->saveAction;
    }

    



    public function getUpdateHistoryAction(): ChangeHistoryActionInterface
    {
        if ($this->disabled) {
            return new EmptyAction($this);
        }
        $this->actionType = ChangeHistoryEntityClass::CHANGE_TYPE_UPDATE;
        $this->saveAction->setIsInsert(false);
        return $this->saveAction;
    }

    public function persistChangeHistoryEntity(): ChangeHistoryEntityClass
    {
        return ChangeHistoryManager::persistChangeHistoryEntity($this->getEntity(), $this->actionType);
    }

    


    public function setEntityIdentifier(?string $entityIdentifier): void
    {
        $this->entityIdentifier = $entityIdentifier;
    }

    


    public function getEntityIdentifier(): ?string
    {
        return $this->entityIdentifier;
    }

    


    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    


    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

}