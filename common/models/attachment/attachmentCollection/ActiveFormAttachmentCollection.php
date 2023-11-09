<?php


namespace common\models\attachment\attachmentCollection;


use common\models\attachment\BaseFileCollectionModel;
use common\models\AttachmentType;
use common\models\User;

class ActiveFormAttachmentCollection extends BaseAttachmentCollection
{
    public $attribute;

    public function __construct(
        AttachmentType $attachmentType,
        User           $user,
                       $attachments = [],
                       $formName = null,
                       $attribute = 'file'
    )
    {
        parent::__construct($attachmentType, $user, $attachments, $formName);
        $this->attribute = $attribute;
    }

    public function getModelEntity(): BaseFileCollectionModel
    {
        $entity = parent::getModelEntity();
        $entity->setSkipOnEmpty($entity->getSkipOnEmpty() || $this->attachments);
        return $entity;
    }


    public function getInputName(): string
    {
        return "{$this->getModelEntity()->formName()}[{$this->attribute}][{$this->getIndex()}]";
    }
}