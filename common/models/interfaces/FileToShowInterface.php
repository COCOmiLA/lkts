<?php


namespace common\models\interfaces;


use common\components\attachmentSaveHandler\interfaces\AttachmentSaveHandlerInterface;
use common\models\attachment\BaseFileCollectionModel;
use common\models\AttachmentType;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeHistoryClassTypeInterface;

interface FileToShowInterface extends ChangeHistoryClassTypeInterface
{
    


    public function isHidden(): ?bool;

    


    public function isRequired(): ?bool;

    


    public function getAttachmentTypeName(): ?string;

    


    public function getSendingProperties(): array;

    


    public function getModelEntity(): BaseFileCollectionModel;

    


    public function getInitialPreviewConfig() : array;

    


    public function getFileDownloadUrl(): ?string;

    public function getFileDeleteUrl(): ?string;

    


    public function getInitialPreviews(): array;

    


    public function getAttachmentType(): ?AttachmentType;

    


    public function getIndex();

    


    public function getInputName(): string;

    


    public function getAttachmentSaveHandler(): AttachmentSaveHandlerInterface;
}