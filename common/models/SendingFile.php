<?php

namespace common\models;

use common\components\AttachmentManager;
use common\components\soapException;
use common\models\dictionary\DocumentType;
use common\modules\abiturient\models\File;
use Exception;

class SendingFile
{
    
    private const CHUNK_SIZE = 20 * 1024 * 1024; 

    
    public $transfer_id;

    
    public $parts = [];

    
    public $file;

    public function __construct(File $file)
    {
        $this->file = $file;
        $this->transfer_id = SendingFile::generateGuid();
        $file_path = $file->getFilePath();
        if (file_exists($file_path)) {
            $bin = file_get_contents($file_path);
            $this->loadNewFile($bin);
        }
    }

    public static function SplitFile(string $bin, string $transfer_id)
    {
        $result = [];
        $full_length = strlen((string)$bin);
        $chunkSize = SendingFile::getChunkSize();
        for (
            $offset = 0, $chunk_number = 1;
            $offset < $full_length;
            $chunk_number++, $offset += $chunkSize
        ) {
            $result[] = new FilePart(substr($bin, $offset, $chunkSize), $chunk_number, $transfer_id);
        }

        return $result;
    }

    


    protected function loadNewFile($bin)
    {
        $this->parts = SendingFile::SplitFile($bin, $this->transfer_id);
    }

    




    public static function generateGuid()
    {
        $guid = '';
        $namespace = random_int(11111, 99999);
        $uid = uniqid('', true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        return substr($hash, 0, 8) . '-' .
            substr($hash, 8, 4) . '-' .
            substr($hash, 12, 4) . '-' .
            substr($hash, 16, 4) . '-' .
            substr($hash, 20, 12);
    }

    






    public function saveFileTo1C(): bool
    {
        foreach ($this->parts as $part) {
            $result = $part->sendFileTo1C();
            if (!$result) {
                return false;
            }
        }
        return true;
    }

    public function getPartsArraysTo1C(): array
    {
        $res = [];
        foreach ($this->parts as $part) {
            $res[] = $part->buildArrayTo1C();
        }
        return $res;
    }

    







    public function buildArrayTo1C(DocumentType $docType, ?string $custom_file_name): ?array
    {
        if ($this->saveFileTo1C()) {
            return AttachmentManager::buildFileTo1C($this->file, $docType, $custom_file_name, $this->getPartsArraysTo1C());
        }
        return null;
    }

    


    public static function getChunkSize(): int
    {
        $chunkSize = getenv('SENDING_FILE_CHUNK_SIZE');
        if (EmptyCheck::isEmpty($chunkSize) || !is_numeric($chunkSize)) {
            return static::CHUNK_SIZE;
        }

        return intval($chunkSize);
    }
}
