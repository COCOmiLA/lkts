<?php

namespace common\modules\abiturient\models\interfaces;

use common\models\dictionary\DocumentType;
use common\modules\abiturient\models\File;

interface ICanAttachFile
{
    public function attachFile(IReceivedFile $receivingFile, DocumentType $documentType): ?File;

    



    public function removeNotPassedFiles(array $file_ids_to_ignore);

    public function getAttachedFilesInfo(): array;
}