<?php

namespace common\components\attachmentSaveHandler;


use common\components\attachmentSaveHandler\exceptions\AttachmentViolationException;
use common\components\attachmentSaveHandler\interfaces\AttachmentSaveHandlerInterface;
use common\components\ChangeHistoryManager;
use common\components\IdentityManager\IdentityManager;
use common\models\Attachment;
use common\models\EntrantManager;
use common\models\interfaces\FileToShowInterface;
use common\models\MasterSystemManager;
use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryEntityClass;
use yii\base\UserException;
use yii\web\UploadedFile;

class BaseAttachmentSaveHandler implements AttachmentSaveHandlerInterface
{

    


    private $entity;

    


    private $historyInitiator;

    


    private $historyInitiatorManager;

    


    private $owner;

    public function __construct(FileToShowInterface $entity, User $owner)
    {
        $this->setEntity($entity);
        $this->setOwner($owner);

        $initiator = IdentityManager::GetIdentityForHistory();

        if (!is_null($initiator)) {
            if (($initiator instanceof User && $initiator->isModer()) || ($initiator instanceof MasterSystemManager)) {
                $this->setHistoryInitiatorManager($initiator->getEntrantManagerEntity());
            }

            if ($initiator instanceof User) {
                $this->setHistoryInitiator($initiator);
            }
        }


    }

    public function save(): array
    {
        $newAttachments = [];
        $files = UploadedFile::getInstancesByName($this->getEntity()->getInputName());
        $transaction = \Yii::$app->db->beginTransaction();

        if ($transaction === null) {
            throw new UserException('Невозможно создать транзакцию');
        }
        try {
            if ($files) {
                $change = $this->saveChangeHistory();
                foreach ($files as $file) {
                    $attachment = new Attachment();
                    $attachment = $this->prepareAttachment($attachment);
                    $attachment->file = $file;
                    if (!$attachment->upload()) {
                        if ($attachment->hasErrors()) {
                            throw new AttachmentViolationException($attachment->file->getBaseName(), $attachment->errors);
                        }
                        throw new UserException('Невозможно сохранить файл.');
                    }

                    if (!is_null($change)) {
                        $this->saveChangeHistoryClass($attachment, $change);
                    }

                    $newAttachments[] = $attachment;
                }
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return $newAttachments;
    }

    


    public function getEntity(): FileToShowInterface
    {
        return $this->entity;
    }

    


    public function setEntity(FileToShowInterface $entity): void
    {
        $this->entity = $entity;
    }

    


    public function getOwner(): User
    {
        return $this->owner;
    }

    


    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    





    protected function prepareAttachment(Attachment $attachment): Attachment
    {
        if (!$this->getOwner() || $this->getOwner()->getIsNewRecord()) {
            throw new UserException('При сохранении файла произошла ошибка.');
        }

        $attachment->attachment_type_id = $this->getEntity()->getAttachmentType()->id;
        $attachment->owner_id = $this->getOwner()->getId();
        return $attachment;
    }

    protected function saveChangeHistoryClass(Attachment $attachment, ChangeHistory $change)
    {
        if (empty($this->getHistoryInitiator())) {
            return null;
        }
        $class = ChangeHistoryManager::persistChangeHistoryEntity($attachment, ChangeHistoryEntityClass::CHANGE_TYPE_INSERT);
        $class->setChangeHistory($change);

        if ($class->validate()) {
            $class->save(false);
        } else {
            throw new UserException('Ошибка записи историй изменений. Невозможно сохранить класс истории изменения.');
        }

        foreach (ChangeHistoryManager::persistChangeHistoryEntityInputs($class, $attachment, false) as $detail) {
            $detail->setEntityClass($class);
            if ($detail->validate()) {
                $detail->save(false);
            } else {
                throw new UserException('Ошибка записи историй изменений. Невозможно сохранить детали изменений.');
            }
        }
    }

    protected function saveChangeHistory(): ?ChangeHistory
    {
        if (empty($this->getHistoryInitiator())) {
            return null;
        }

        $change = $this->prepareChange();
        if ($change->validate()) {
            $change->save(false);
        } else {
            throw new UserException('Ошибка записи историй изменения. Невозможно сохранить данные.');
        }
        return $change;
    }

    



    protected function prepareChange(): ChangeHistory
    {
        return ChangeHistoryManager::persistChangeForEntity($this->getHistoryInitiator(), ChangeHistory::CHANGE_HISTORY_FILE);
    }

    public function setHistoryInitiator(?User $user): void
    {
        $this->historyInitiator = $user;
    }

    


    public function getHistoryInitiator(): ?User
    {
        return $this->historyInitiator;
    }

    public function setHistoryInitiatorManager(?EntrantManager $user)
    {
        $this->historyInitiatorManager = $user;
    }

    public function getHistoryInitiatorManager(): ?EntrantManager
    {
        return $this->historyInitiatorManager;
    }
}