<?php

namespace common\modules\abiturient\models\services;

use common\components\AttachmentManager;
use common\models\dictionary\DocumentType;
use common\models\interfaces\FileToSendInterface;
use common\models\SendingFile;
use common\models\ToAssocCaster;
use common\models\User;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\ReceivingFile;
use geoffry304\enveditor\exceptions\FileNotFoundException;
use Throwable;
use Yii;
use yii\helpers\FileHelper;

class FullPackageFilesSyncer extends \yii\base\BaseObject
{
    protected $application;
    protected $questionary;
    


    protected $sent_files_with_info = [];
    protected $received_files = [];
    protected $files_sent = false;
    protected $processed_raw_files = [];

    public function __construct(?BachelorApplication $application)
    {
        parent::__construct();
        $this->application = $application;
    }

    public function setQuestionary(AbiturientQuestionary $questionary): FullPackageFilesSyncer
    {
        $this->questionary = $questionary;
        return $this;
    }

    



    public function isFilesSyncing(): bool
    {
        return $this->files_sent;
    }

    public static function BuildFilesInfoToList(array $files_info_list)
    {
        $data = [];
        foreach ($files_info_list as [$file_instance, $documentType, $filename]) {
            
            
            if ($file_instance->linkedFile && $file_instance->linkedFile->fileExists()) {
                $data[] = FullPackageFilesSyncer::fileInfoInto1CFormat($file_instance, $filename);
            }
        }
        return $data;
    }

    


    public function GetFilesNeedsToSend(): array
    {
        $files_info_list = $this->getApplicationFilesInfo();

        $files_need_to_send = FullPackageFilesSyncer::BuildFilesInfoToList($files_info_list);
        if ($this->getUser()->userRef) {
            $files_need_to_send = $this->postFilesList($files_need_to_send);
        }
        return $this->filterFileInfosBy1CRequest($files_info_list, $files_need_to_send);
    }

    






    public function buildFileInfoTo1C(FileToSendInterface $fileInstance, DocumentType $documentType, ?string $filename): array
    {
        
        $built_parts = $this->findProcessedFileParts($fileInstance);
        return AttachmentManager::buildFileTo1C($fileInstance->linkedFile, $documentType, $filename, $built_parts);
    }

    




    public function SendFiles()
    {
        $files_info = $this->GetFilesNeedsToSend();

        $result = [];
        foreach ($files_info as [$fileWithLinkedFile, $custom_file_name]) {
            $file = $fileWithLinkedFile->linkedFile;
            if (!$file) {
                throw new FileNotFoundException();
            }
            $sending_file = new SendingFile($file);
            if ($sending_file->saveFileTo1C()) {
                $built_parts = $sending_file->getPartsArraysTo1C();
                if (!empty($built_parts)) {
                    $result[] = [$fileWithLinkedFile, $custom_file_name, $built_parts];
                }
            }
        }
        $this->sent_files_with_info = $result;
        $this->files_sent = true;
    }

    public function FetchMissingFiles()
    {
        $received_files = [];
        try {
            $files_in_1c = $this->getFilesList();
            $files_info_list = $this->getApplicationFilesInfo();
            foreach ($files_in_1c as $file_from_1c) {
                if (!FullPackageFilesSyncer::FindInfoBy1CFile($files_info_list, $file_from_1c)) {
                    $receiving_file = new ReceivingFile($file_from_1c);
                    $receiving_file->fetchFile();
                    $received_files[] = $receiving_file;
                }
            }
        } catch (Throwable $e) {
            
            foreach ($received_files as $received_file) {
                $received_file->removeTempFiles();
            }
            throw $e;
        }
        $this->received_files = $received_files;
    }

    




    public static function FindInfoBy1CFile(array $local_files_info_list, \stdClass $file_from_1c)
    {
        foreach ($local_files_info_list as $local_file_info) {
            $fileInstance = $local_file_info[0];
            $documentType = $local_file_info[1];

            if (!$fileInstance->linkedFile) {
                continue;
            }
            if ($file_from_1c->FileUID && $fileInstance->linkedFile->uid && $fileInstance->linkedFile->uid != $file_from_1c->FileUID) {
                continue;
            }
            if ($fileInstance->linkedFile->content_hash === $file_from_1c->FileHash) {
                
                if (!isset($file_from_1c->FileTypeRef) || $documentType->ref_key == ((object)$file_from_1c->FileTypeRef)->ReferenceUID) {
                    if ($fileInstance->linkedFile->fileExists()) {
                        return $local_file_info;
                    }
                }
            }
        }
        return null;
    }

    public function FindReceivedFile(
        string $file_name,
        string $file_extension,
        string $file_hash,
        string $file_uid
    ): ?ReceivingFile {
        $file_full_name = "{$file_name}.{$file_extension}";
        foreach ($this->received_files as $received_file) {
            
            if (
                $received_file->uploadName == $file_full_name
                && $received_file->hash === $file_hash
                && (!$file_uid || !$received_file->fileUID || $file_uid == $received_file->fileUID)
            ) {
                return $received_file;
            }
        }
        return null;
    }

    public function appendReceivedFile(ReceivingFile $received_file)
    {
        $this->received_files[] = $received_file;
    }

    


    public function ClearReceivedFiles()
    {
        foreach ($this->received_files as $file) {
            
            $file->removeTempFiles();
        }
        $this->received_files = [];
        foreach ($this->_additional_temp_files as $temp_file) {
            if (file_exists($temp_file) && !is_dir($temp_file)) {
                try {
                    FileHelper::unlink($temp_file);
                } catch (Throwable $e) {
                    Yii::error("Не удалось очистить временный файл {$temp_file} по причине: {$e->getMessage()}");
                }
            }
        }
        $this->_additional_temp_files = [];
    }

    private $_additional_temp_files = [];

    public function appendAdditionalTempFiles(array $temp_files)
    {
        $this->_additional_temp_files = [...$this->_additional_temp_files, ...$temp_files];
    }

    




    protected function findProcessedFileParts(FileToSendInterface $fileToSend): ?array
    {
        $buildInfo = null;
        if ($fileToSend->linkedFile) {
            foreach ($this->sent_files_with_info as [$fileWithLinkedFile, $custom_file_name, $built_parts]) {
                if ($fileWithLinkedFile->id == $fileToSend->id && $fileWithLinkedFile::tableName() == $fileToSend::tableName()) {
                    $buildInfo = $built_parts;
                    break;
                }
            }
        }
        return $buildInfo;
    }

    






    protected static function fileInfoInto1CFormat(FileToSendInterface $fileInstance, ?string $filename): array
    {
        return [
            'FileName' => $filename ?? pathinfo($fileInstance->filename)['filename'],
            'FileExt' => $fileInstance->extension,
            'FileUID' => $fileInstance->linkedFile->uid,
            'FileHash' => $fileInstance->linkedFile->content_hash,
            'FilePartsCount' => $fileInstance->linkedFile->partsCount,
        ];
    }

    





    protected function filterFileInfosBy1CRequest(array $files_info_list, array $files_info_from_1c): array
    {
        $result = [];
        foreach ($files_info_list as [$file_instance, $_, $filename]) {
            if (array_filter($files_info_from_1c, function ($file) use ($file_instance, $filename) {
                $assoc_file = ToAssocCaster::getAssoc($file);
                $linkedFile = $file_instance->linkedFile;
                if (!$linkedFile) {
                    return false;
                }
                if ($assoc_file['FileUID'] && $linkedFile->uid && $assoc_file['FileUID'] != $linkedFile->uid) {
                    return false;
                }
                return $assoc_file['FileHash'] == $linkedFile->content_hash
                    && ($filename ?? pathinfo($linkedFile->upload_name)['filename']) == $assoc_file['FileName']
                    && $file_instance->extension == $assoc_file['FileExt'];
            })) {
                $result[] = [$file_instance, $filename];
            }
        }
        return $result;
    }

    public function getApplicationFilesInfo(): array
    {
        return $this->application->getAllAttachmentsInfo();
    }

    protected function postFilesList(array $files): array
    {
        $result = Yii::$app->soapClientWebApplication->load('PostFilesList', [
            'Entrant' => $this->application->buildEntrantArray(),
            'Files' => $files
        ]);

        if (isset($result->return) && isset($result->return->FileInfo)) {
            if (!is_array($result->return->FileInfo)) {
                $result->return->FileInfo = array_values(array_filter([$result->return->FileInfo]));
            }
            return $result->return->FileInfo;
        }
        return [];
    }

    protected function getFilesList(): array
    {
        $result = Yii::$app->soapClientWebApplication->load('GetFilesList', [
            'Entrant' => $this->application->buildEntrantArray(),
        ]);

        if (isset($result->return) && isset($result->return->FileInfo)) {
            if (!is_array($result->return->FileInfo)) {
                $result->return->FileInfo = array_values(array_filter([$result->return->FileInfo]));
            }
            return $result->return->FileInfo;
        }
        return [];
    }

    public function getProcessedRawFiles(): array
    {
        return $this->processed_raw_files;
    }

    public function appendProcessedRawFile(array $file)
    {
        $this->processed_raw_files[] = $file;
    }

    protected function getUser(): User
    {
        return $this->application->user ?? $this->questionary->user;
    }
}
