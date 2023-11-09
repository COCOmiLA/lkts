<?php

namespace common\models\traits;

use common\components\AttachmentManager;
use common\models\Attachment;
use common\models\dictionary\DocumentType;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\interfaces\IReceivedFile;

trait FileAttachTrait
{
    public function attachFile(IReceivedFile $receivingFile, DocumentType $documentType): ?File
    {
        $attachment = AttachmentManager::AttachFileToLinkableEntity($this, $receivingFile);

        return $attachment->linkedFile;
    }

    public function removeNotPassedFiles(array $file_ids_to_ignore)
    {
        $ignored_attachment_ids = $this->getAttachments()
            ->select(['MAX(attachment.id) id'])
            ->joinWith(['linkedFile linked_file'])
            ->joinWith('attachmentType')
            ->andWhere(['linked_file.id' => $file_ids_to_ignore])
            ->groupBy(['linked_file.id', 'attachment_type.id']);

        
        $attachments_to_delete = $this->getAttachments()
            ->joinWith(['linkedFile linked_file'])
            ->andWhere(['not', ['attachment.id' => $ignored_attachment_ids]])
            ->all();
        foreach ($attachments_to_delete as $attachment_to_delete) {
            $attachment_to_delete->silenceSafeDelete();
        }
    }
}