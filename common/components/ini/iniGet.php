<?php

namespace common\components\ini;

class iniGet
{
    








    public static function getUploadMaxFilesize($inKB = true)
    {
        $val = self::getUploadMaxFilesizeString();

        $size = self::sizeToBytes($val);
        if ($inKB) {
            $size = floor($size / 1024);
        }

        return $size;
    }

    public static function getUploadMaxFilesizeString()
    {
        $val = (substr(ini_get('post_max_size'), 0, strlen(ini_get('post_max_size')) - 1) < substr(ini_get('upload_max_filesize'), 0, strlen(ini_get('upload_max_filesize')) - 1)) ? ini_get('post_max_size') : ini_get('upload_max_filesize');
        return trim((string)$val);
    }

    







    public static function getUploadSizeLimitMultiple(): int
    {
        $postLimit = self::sizeToBytes(ini_get('post_max_size'));
        if (0 === $postLimit) {
            return 0;
        }

        $limit = self::sizeToBytes(ini_get('upload_max_filesize'));
        if ($postLimit < $limit) {
            \Yii::warning('PHP.ini\'s \'post_max_size\' is less than \'upload_max_filesize\'.', __METHOD__);
            return $postLimit;
        }

        $maxFileUploads = (int) ini_get('max_file_uploads');
        $multipleLimit = $limit * $maxFileUploads;

        return ($multipleLimit < $postLimit) ? $multipleLimit : $postLimit;
    }

    





    public static function getMaximumFileUploadsNumber(): int
    {
        return (int) ini_get('max_file_uploads');
    }

    private static function sizeToBytes(string $sizeStr): int
    {
        $last = strtolower(substr($sizeStr, -1));
        $val = (int) $sizeStr;
        switch ($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }
}
