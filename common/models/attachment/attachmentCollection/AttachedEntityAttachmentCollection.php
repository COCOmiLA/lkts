<?php


namespace common\models\attachment\attachmentCollection;


use common\components\attachmentSaveHandler\AttachedAttachmentSaveHandler;
use common\models\AttachmentType;
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\User;

class AttachedEntityAttachmentCollection extends ActiveFormAttachmentCollection
{
    


    private $_entity;

    









    public function __construct(
        User                     $user,
        AttachmentLinkableEntity $en,
        AttachmentType           $attachmentType,
                                 $attachments = [],
                                 $formName = null,
                                 $attribute = 'file',
                                 $customRecordIndex = "X"
    )
    {
        parent::__construct($attachmentType, $user, $attachments, $formName, $attribute);
        $this->setEntity($en);
        $this->setIndex($en->getIsActuallyNewRecord() ? $customRecordIndex : $en->id);

        $this->setAttachmentSaveHandler(new AttachedAttachmentSaveHandler($this, $en));
    }

    public function getAttachmentTypeName(): ?string
    {
        $entity = $this->getEntity();
        if ($entity) {
            $name = $entity->getName();
            if ($name) {
                return $name;
            }
        }
        return parent::getAttachmentTypeName();
    }

    public function getSendingProperties(): array
    {
        $entity = null;
        if ($this->getEntity() !== null) {
            $entity = $this->getEntity();
        }
        return array_merge(parent::getSendingProperties(),
            [
                'entity_id' => $entity !== null ? $entity->id : null
            ]);
    }

    


    public function getEntity(): AttachmentLinkableEntity
    {
        return $this->_entity;
    }

    public function setEntity(AttachmentLinkableEntity $entity)
    {
        $this->_entity = $entity;
    }
}