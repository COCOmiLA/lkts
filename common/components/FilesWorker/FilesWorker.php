<?php

namespace common\components\FilesWorker;

use Throwable;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;




class FilesWorker
{
    private static $IGNORED_FILES = ['.', '..', '.gitignore'];

    


    public function __construct(array $ignoredFiles = [])
    {
        if (!empty($ignoredFiles)) {
            FilesWorker::$IGNORED_FILES = ArrayHelper::merge(
                FilesWorker::$IGNORED_FILES,
                $ignoredFiles
            );
        }
    }

    


    public static function getAllowedExtensionsToUploadList(): array
    {
        return ['png', 'jpg', 'doc', 'docx', 'pdf', 'bmp', 'jpeg'];
    }

    


    public static function getAllowedImageExtensionsToUploadList(): array
    {
        return ['png', 'jpg', 'bmp', 'jpeg', 'svg'];
    }

    




    private static function deleteDir($dirPath): bool
    {
        if (!FilesWorker::eraseDir($dirPath)) {
            return false;
        }

        try {
            return rmdir($dirPath);
        } catch (Throwable $th) {
            return false;
        }
    }

    




    private static function eraseDir($dirPath): bool
    {
        if (!is_dir($dirPath)) {
            return false;
        }
        if ($dirPath[-1] != DIRECTORY_SEPARATOR) {
            $filesPath = FileHelper::normalizePath("{$dirPath}/*");
        } else {
            $filesPath = "{$dirPath}*";
        }
        $files = glob($filesPath, GLOB_MARK);
        foreach ($files as $file) {
            if (!FilesWorker::deleteByPath($file)) {
                return false;
            }
        }

        return true;
    }

    




    private static function deleteByPath($path): bool
    {
        if (is_link($path)) {
            return true;
        }

        if (is_dir($path)) {
            $hasError = !FilesWorker::deleteDir($path);
        } else {
            try {
                $hasError = !unlink($path);
            } catch (Throwable $th) {
                return false;
            }
        }
        if ($hasError) {
            return false;
        }

        return true;
    }

    





    public static function purgeDirectoryContent(string $path, array $ignoredFiles = []): bool
    {
        if (!empty($ignoredFiles)) {
            $notUnlinkedFiles = ArrayHelper::merge(
                FilesWorker::$IGNORED_FILES,
                $ignoredFiles
            );
        } else {
            $notUnlinkedFiles = FilesWorker::$IGNORED_FILES;
        }

        $files = scandir($path);
        if (!empty($files)) {
            $hasError = false;
            foreach ($files as $file) {
                if (in_array($file, $notUnlinkedFiles)) {
                    continue;
                }

                $unlinkedPath = FileHelper::normalizePath("{$path}/$file");
                $hasErrorUnlink = !FilesWorker::deleteByPath($unlinkedPath);
                if ($hasErrorUnlink && !$hasError) {
                    $hasError = true;
                }
            }

            return !$hasError;
        }

        return true;
    }

    




    public static function hasFile($path)
    {
        $path = FileHelper::normalizePath($path);

        if (is_dir($path)) {
            return false;
        }

        return file_exists($path);
    }
}
