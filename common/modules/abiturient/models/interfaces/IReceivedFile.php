<?php

namespace common\modules\abiturient\models\interfaces;

use common\modules\abiturient\models\File;







interface IReceivedFile
{
    public function getFileUID(): string;

    public function getHash(): string;

    public function getFileContent(): string;

    public function getUploadName(): string;

    public function getExtension(): string;

    public function getFile(ICanGetPathToStoreFile $entity_to_link): File;
}