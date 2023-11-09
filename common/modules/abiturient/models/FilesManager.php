<?php

namespace common\modules\abiturient\models;

use common\components\AttachmentManager;
use common\components\filesystem\FilterFilename;
use common\components\LikeQueryManager;
use common\models\interfaces\AttachmentLinkableEntity;
use Yii;
use yii\helpers\FileHelper;

class FilesManager
{
    public static function FindFile(
        string  $upload_name,
        string  $content_hash,
        ?string $file_uid,
        ?string $base_path
    ): ?File {
        $upload_name = FilterFilename::sanitize($upload_name);

        return File::find()
            ->where([
                'upload_name' => $upload_name,
                'content_hash' => $content_hash,
                'uid' => $file_uid
            ])
            ->andFilterWhere([
                'base_path' => $base_path
            ])
            ->one();
    }

    public static function FindFileWithExistingContent(
        string  $upload_name,
        string  $content_hash,
        ?string $file_uid,
        ?string $base_path
    ): ?File {
        $upload_name = FilterFilename::sanitize($upload_name);

        $files = File::find()
            ->where([
                'upload_name' => $upload_name,
                'content_hash' => $content_hash,
                'uid' => $file_uid ?: null
            ])
            ->andFilterWhere([
                'base_path' => $base_path
            ])
            ->all();

        foreach ($files as $file) {
            if ($file->fileExists()) {
                return $file;
            }
        }
        return null;
    }

    public static function FindFileWithExistingContentWithoutFileNameCheck(
        string  $extension,
        string  $content_hash,
        ?string $base_path
    ): ?File {
        $files = File::find()
            ->where([LikeQueryManager::getActionName(), 'upload_name', "%.{$extension}", false])
            ->andWhere([
                'content_hash' => $content_hash,
            ])
            ->andFilterWhere([
                'base_path' => $base_path
            ])
            ->all();

        foreach ($files as $file) {
            if ($file->fileExists()) {
                return $file;
            }
        }
        return null;
    }

    public static function CalculateFileHash(File $file): ?string
    {
        return FilesManager::GetFileHash($file->getFilePath());
    }

    public static function GetFileHash(string $path): ?string
    {
        $hash = null;
        try {
            $hash = md5_file($path);
        } catch (\Throwable $e) {
            return null;
        }
        return $hash ? mb_strtoupper($hash) : null;
    }

    





    public static function EnsureDirectoryExists(string $path): void
    {
        $path = FileHelper::normalizePath($path);

        if (!file_exists($path)) {
            try {
                FileHelper::createDirectory($path);
            } catch (\Throwable $e) {
                Yii::error("Не удалось создать директорию {$path} по причине: {$e->getMessage()}");
                throw $e;
            }
        }
    }

    public static function CopyFiles(AttachmentLinkableEntity $from, AttachmentLinkableEntity $to): void
    {
        foreach ($from->getAttachments()->all() as $attachment) {
            $exists_file = $attachment->linkedFile;
            if (!$exists_file) {
                continue;
            }
            $received_file = new AlreadyReceivedFile(
                $exists_file,
                null,
                $exists_file->upload_name,
                $exists_file->content_hash,
                null
            );
            AttachmentManager::AttachFileToLinkableEntity($to, $received_file);
        }
    }
}
