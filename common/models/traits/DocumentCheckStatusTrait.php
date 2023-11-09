<?php


namespace common\models\traits;

use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\Attachment;
use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use common\models\interfaces\AttachmentLinkableEntity;
use Yii;
use yii\base\UserException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

trait DocumentCheckStatusTrait
{
    


    public function getDocumentCheckStatusRefType(): ActiveQuery
    {
        return $this->hasOne(StoredDocumentCheckStatusReferenceType::class, ['id' => 'document_check_status_ref_id']);
    }

    


    public function getDocumentCheckStatus(): string
    {
        $documentCheckStatusRefType = $this->documentCheckStatusRefType;
        return $documentCheckStatusRefType ? $documentCheckStatusRefType->humanReadableName : '';
    }

    




    public function getDocumentCheckStatusIcon(string $text = ''): string
    {
        $documentCheckStatusRefType = $this->documentCheckStatusRefType;
        return $documentCheckStatusRefType ? $documentCheckStatusRefType->getIcon($text) : '';
    }

    


    public function fillDocumentCheckStatusIfNotVerified(): bool
    {
        if ($this->document_check_status_ref_id) {
            return true;
        }

        $this->setDocumentCheckStatusNotVerified();

        return true;
    }

    


    public function setDocumentCheckStatusNotVerified(): void
    {
        $notVerifiedStatusDocumentCheck = $this->getNotVerifiedStatusDocumentChecker();
        if ($notVerifiedStatusDocumentCheck) {
            $this->document_check_status_ref_id = $notVerifiedStatusDocumentCheck->id;
        }
    }

    


    public function getNotVerifiedStatusDocumentChecker(): ?StoredDocumentCheckStatusReferenceType
    {
        return StoredDocumentCheckStatusReferenceType::findOne([
            'id' => Yii::$app->configurationManager->getCode('not_verified_status_document_checker')
        ]);
    }

    


    public function buildAttachmentHash(): array
    {
        if (!$this instanceof AttachmentLinkableEntity) {
            return [];
        }

        $attachments = $this->getAttachments()->all();

        $attachedFileHashList = [];
        foreach ($attachments as $attachment) {
            

            if ($linkedFile = $attachment->linkedFile) {
                $attachedFileHashList[] = $linkedFile->real_file_name;
            }
        }

        return $attachedFileHashList;
    }

    





    public function checkIfDocumentIsChanged(array $attachedFileHashList, bool $checkAttachments = true): bool
    {
        if ($this->read_only || $this->isNewRecord) {
            return true;
        }

        $notVerifiedStatusDocumentCheck = $this->getNotVerifiedStatusDocumentChecker();
        if (
            $notVerifiedStatusDocumentCheck &&
            $this->document_check_status_ref_id == $notVerifiedStatusDocumentCheck->id
        ) {
            return true;
        }

        $existAttachedFileHashList = [];
        if ($checkAttachments) {
            $existAttachedFileHashList = $this->buildAttachmentHash();

            if (count($existAttachedFileHashList) != count($attachedFileHashList)) {
                return false;
            }
        }

        $attributes = $this->getAttributes();
        unset($attributes['updated_at']);
        $oldAttributes = $this->getOldAttributes();
        unset($oldAttributes['updated_at']);

        $diff = array_merge(
            array_diff($attributes, $oldAttributes),
            array_diff($oldAttributes, $attributes),
            array_diff($existAttachedFileHashList, $attachedFileHashList),
            array_diff($attachedFileHashList, $existAttachedFileHashList),
        );

        if (count($diff) > 0) {
            return false;
        }

        return true;
    }

    




    public function convertFlagAccordingDocumentStatus(bool $externalFlagAllowsSomething = true): bool
    {
        return $externalFlagAllowsSomething && !$this->read_only;
    }

    




    public function buildDocumentCheckStatusRefType()
    {
        $notVerifiedStatusDocumentChecker = ArrayHelper::getValue($this, 'notVerifiedStatusDocumentChecker');
        $documentCheckStatusRefType = ArrayHelper::getValue($this, 'documentCheckStatusRefType') ?? $notVerifiedStatusDocumentChecker;
        if (!$documentCheckStatusRefType) {
            throw new UserException('Не удалось собрать структуру статуса документа.');
        }

        return ReferenceTypeManager::GetReference($documentCheckStatusRefType);
    }

    




    public function setDocumentCheckStatusFrom1CData(array $rawDocumentCheckStatus): void
    {
        if (!$rawDocumentCheckStatus) {
            return;
        }

        $documentCheckStatusRef = ReferenceTypeManager::GetOrCreateReference(
            StoredDocumentCheckStatusReferenceType::class,
            $rawDocumentCheckStatus
        );
        if (!$documentCheckStatusRef) {
            return;
        }

        $this->document_check_status_ref_id = $documentCheckStatusRef->id;
    }
}
