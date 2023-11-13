<?php

namespace common\components\attachmentSaveHandler;


use common\components\AttachmentManager;
use common\components\attachmentSaveHandler\exceptions\AttachmentViolationException;
use common\models\Attachment;
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\interfaces\FileToShowInterface;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use yii\base\UserException;
use yii\web\UploadedFile;





class AttachedAttachmentSaveHandler extends BaseAttachmentSaveHandler
{
    


    private $attachedEntity;

    




    public function __construct(FileToShowInterface $entity, AttachmentLinkableEntity $attachedEntity)
    {
        parent::__construct($entity, $attachedEntity->getUserInstance());
        $this->setAttachedEntity($attachedEntity);
    }

    


    public function getAttachedEntity(): AttachmentLinkableEntity
    {
        return $this->attachedEntity;
    }

    


    public function setAttachedEntity(AttachmentLinkableEntity $attachedEntity): void
    {
        $this->attachedEntity = $attachedEntity;
    }

    protected function prepareAttachment(Attachment $attachment): Attachment
    {
        $newAttachment = parent::prepareAttachment($attachment);
        $newAttachment->attributes = $this->getAttachedEntity()->getAttachmentConnectors();
        return $newAttachment;
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
                    $attachment->_initAttachedEntity();

                    AttachmentManager::linkAttachment($this->getAttachedEntity(), $attachment);

                    if ($change) {
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

    protected function prepareChange(): ChangeHistory
    {
        $change = parent::prepareChange();
        if ($this->attachedEntity instanceof ApplicationConnectedInterface) {
            $change->application_id = $this->attachedEntity->application->id;
        } elseif ($this->attachedEntity instanceof QuestionaryConnectedInterface) {
            $change->questionary_id = $this->attachedEntity->abiturientQuestionary->id;
        }

        return $change;
    }
}