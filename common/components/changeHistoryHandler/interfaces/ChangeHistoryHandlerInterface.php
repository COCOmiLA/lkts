<?php

namespace common\components\changeHistoryHandler\interfaces;


use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;

interface ChangeHistoryHandlerInterface
{
    public function persistChange() : ChangeHistory;

    public function persistChangeHistoryEntity() : ChangeHistoryEntityClass;

    public function getEntity(): ChangeLoggedModelInterface;

    public function setEntity(ChangeLoggedModelInterface $entity): void;

    public function getDeleteHistoryAction(): ChangeHistoryActionInterface;

    public function getUpdateHistoryAction(): ChangeHistoryActionInterface;

    public function getInsertHistoryAction(): ChangeHistoryActionInterface;

    public function getInitiator(): ?User;

    public function setInitiator($user);

    public function getActionType(): int;

    public function getEntityIdentifier(): ?string;

    public function setEntityIdentifier(?string $entityIdentifier);

    public function getDisabled(): bool;

    public function setDisabled(bool $disabled);

}