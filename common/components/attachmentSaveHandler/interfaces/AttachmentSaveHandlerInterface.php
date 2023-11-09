<?php
namespace common\components\attachmentSaveHandler\interfaces;


use common\components\attachmentSaveHandler\exceptions\AttachmentViolationException;
use common\models\EntrantManager;
use common\models\interfaces\FileToShowInterface;
use common\models\User;

interface AttachmentSaveHandlerInterface
{
    



    public function save(): array;

    public function getEntity(): FileToShowInterface;

    public function setEntity(FileToShowInterface $entity);

    public function getOwner() : User;

    public function setOwner(User $owner);

    public function setHistoryInitiator(User $user);

    public function getHistoryInitiator(): ?User;

    public function setHistoryInitiatorManager(?EntrantManager $user);

    public function getHistoryInitiatorManager(): ?EntrantManager;
}