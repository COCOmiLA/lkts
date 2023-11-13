<?php

namespace common\modules\abiturient\models;

use common\models\dictionary\DocumentType;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use yii\base\BaseObject;

class AlreadyReceivedFile extends BaseObject implements interfaces\IReceivedFile
{
    protected $file_itself;
    protected $document_type;
    protected $file_name;
    protected $file_hash;
    protected $file_uid;

    public function __construct(
        File          $file,
        ?DocumentType $documentType,
        string        $fullFileName,
        string        $fileHash,
        ?string       $fileUID
    )
    {
        parent::__construct();

        $this->file_itself = $file;
        $this->document_type = $documentType;
        $this->file_name = $fullFileName;
        $this->file_hash = $fileHash;
        $this->file_uid = $fileUID;
    }

    public function getFileUID(): string
    {
        return $this->file_uid;
    }

    public function getHash(): string
    {
        return $this->file_hash;
    }

    public function getFileContent(): string
    {
        return (string)$this->file_itself->getFileContent();
    }

    public function getUploadName(): string
    {
        return $this->file_name;
    }

    public function getExtension(): string
    {
        return pathinfo($this->uploadName)['extension'];
    }

    public function getFile(ICanGetPathToStoreFile $entity_to_link): File
    {
        $required_base_path = $entity_to_link->getPathToStoreFiles();
        return $this->file_itself->getCopy($required_base_path, $this->file_uid);
    }

    public function __toString()
    {
        return $this->uploadName;
    }
}