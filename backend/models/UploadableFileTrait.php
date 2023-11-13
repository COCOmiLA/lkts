<?php

namespace backend\models;

use common\models\errors\RecordNotValid;
use common\models\interfaces\FileToSendInterface;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\interfaces\IReceivedFile;
use Probe\Provider\NotImplementedException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

trait UploadableFileTrait
{
    public function upload()
    {
        $path = $this->getPathToStoreFiles();
        $is_valid = $this->validate();
        if ($path && $is_valid) {
            $this->save(false);
            if ($this->file instanceof UploadedFile) {
                $stored_file = File::GetOrCreateByTempFile($path, $this->file);
                $this->LinkFile($stored_file);
            } elseif ($this->file instanceof IReceivedFile) {
                $this->LinkFile($this->file->getFile($this));
            }
            return true;
        }
        if (!$is_valid) {
            throw new RecordNotValid($this);
        }

        return false;
    }

    protected function getOwnerId()
    {
        throw new NotImplementedException('Needs to be overloaded');
    }

    public function getHash()
    {
        $owner_id = $this->getOwnerId();
        return md5($owner_id);
    }

    protected function getBasePathToStoreFiles()
    {
        return File::BASE_PATH;
    }

    public function getPathToStoreFiles(): string
    {
        $hash = $this->getHash();
        $basePath = $this->getBasePathToStoreFiles();
        
        if ($basePath[-1] != '/') {
            $basePath .= '/';
        }
        return FileHelper::normalizePath($basePath . $hash, '/');
    }

    public function getLinkedFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id'])
            ->viaTable(static::getFileRelationTable(), [static::getFileRelationColumn() => 'id']);
    }

    public function LinkFile(File $file)
    {
        if ($this->linkedFile && $this->linkedFile->id == $file->id) {
            return;
        }
        
        $this->deleteAttachedFile();
        $this->link('linkedFile', $file);
    }

    public function getAbsPath(): ?string
    {
        return ArrayHelper::getValue($this, 'linkedFile.filePath');
    }

    public function getExtension(): ?string
    {
        return ArrayHelper::getValue($this, 'linkedFile.extension');
    }

    public function getFilename(): ?string
    {
        return ArrayHelper::getValue($this, 'linkedFile.upload_name', '');
    }

    public function hasFile(): bool
    {
        return boolval($this->linkedFile);
    }

    public function deleteAttachedFile()
    {
        if ($this->hasFile()) {
            $file = $this->linkedFile;
            $this->unlink('linkedFile', $file, true);
            $file->destroyIfNotUsed();
        }
    }

    public function afterDraftCopy(FileToSendInterface $from): void
    {
        if ($this->linkedFile && $this->linkedFile->id != ($from->linkedFile->id ?? null)) {
            $this->unlink('linkedFile', $this->linkedFile, true);
        }
        if ($from->linkedFile) {
            $this->link('linkedFile', $from->linkedFile);
        }
    }
}
