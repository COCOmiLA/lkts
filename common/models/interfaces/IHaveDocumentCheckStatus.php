<?php


namespace common\models\interfaces;

use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use yii\db\ActiveQuery;





interface IHaveDocumentCheckStatus extends AttachmentLinkableEntity
{
    


    public function getDocumentCheckStatusRefType(): ActiveQuery;

    


    public function getDocumentCheckStatus(): string;

    


    public function fillDocumentCheckStatusIfNotVerified(): bool;

    


    public function setDocumentCheckStatusNotVerified(): void;

    


    public function getNotVerifiedStatusDocumentChecker(): ?StoredDocumentCheckStatusReferenceType;

    


    public function buildAttachmentHash(): array;

    





    public function checkIfDocumentIsChanged(array $attachedFileHashList, bool $checkAttachments = true): bool;

    




    public function convertFlagAccordingDocumentStatus(bool $externalFlagAllowsSomething = true): bool;

    




    public function buildDocumentCheckStatusRefType();

    




    public function setDocumentCheckStatusFrom1CData(array $rawDocumentCheckStatus): void;

    




    public function getDocumentCheckStatusIcon(string $text = ''): string;
}
