<?php

namespace common\components\ChecksumManager;

use yii\helpers\Json;

class FilesChecksumReport
{
    
    protected $data = [];
   
    


    public function __construct(string $directory)
    {
        $this->generate($directory);
    }
    
    protected function generate(string $directory): void
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("Аргумент должен указывать на директорию");
        }

        $dir = dir($directory);
        $ignore_dirs = ChecksumManager::getIgnoreDirList();
        $ignore_files = ChecksumManager::getIgnoreFileList();
        
        while (($file = $dir->read()) !== false) {
            if (in_array($file, $ignore_files)) {
                continue;
            }
            
            $fullpath = $directory . DIRECTORY_SEPARATOR . $file;
            if (in_array($fullpath, $ignore_dirs)) {
                continue;
            }

            if (is_link($fullpath)) {
                continue;
            }

            if (is_dir($fullpath)) {
                $this->generate($fullpath);
            } else {
                $relative_path = ChecksumManager::unifyFilename($fullpath);
                $this->data[$relative_path] = ChecksumManager::getFileHash($fullpath);
            }
        }
        
        ksort($this->data);

        $dir->close();
    }
    
    public function getData(): array
    {
        return $this->data;
    }
    
    public function asJson(): string
    {
        return Json::encode($this->data);
    }
}
