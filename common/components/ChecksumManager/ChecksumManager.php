<?php

namespace common\components\ChecksumManager;

use common\components\ChecksumManager\models\Checksum;
use yii\helpers\VarDumper;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

class ChecksumManager
{
    public static function checkVendorChecksum(): bool
    {
        if (!static::needToCheckHashsum()) {
            return true;
        }
        
        $origin_checksum_hash = \Yii::$app->params[Checksum::PARAM_VENDOR] ?? '';
        
        if (empty($origin_checksum_hash)) {
            return true;
        }
        
        $current = static::getCurrentVendorChecksum();
        
        return $current->checksum && $origin_checksum_hash === static::getHashOfChecksum($current->checksum);
    }
    
    



    public static function getCurrentVendorChecksum(): Checksum
    {
        $model = Checksum::getCurrentVendorChecksum();
        
        if ($model === null) {
            $model = new Checksum();
            static::saveChecksum($model);
        }
        
        if (empty($model->checksum)) {
            $time_since_update = time() - $model->updated_at;
            if ($time_since_update > Checksum::LOCK_TIME) {
                static::saveChecksum($model);
            }
        }
        
        return $model;
    }
    
    public static function saveChecksum(Checksum $model): bool
    {
        $model->param = Checksum::PARAM_VENDOR;
        $model->path = static::getVendorPath();
        
        
        $model->status = Checksum::CALC_STATUS_PROCESSING;
        if (!$model->save()) {
            \Yii::error('Не удалось сохранить хеш-сумму' . PHP_EOL . VarDumper::dumpAsString($model->errors), 'CHECKSUM');
            return false;
        }

        
        $model->checksum = static::calculateChecksum($model->path);
        $model->status = Checksum::CALC_STATUS_COMPLETED;
        if (!$model->save()) {
            \Yii::error('Не удалось сохранить хеш-сумму' . PHP_EOL . VarDumper::dumpAsString($model->errors), 'CHECKSUM');
            return false;
        }
        
        return true;
    }
    
    


    public static function initVendorChecksum()
    {
        if (!static::needToCheckHashsum()) {
            return;
        }
        
        if (empty(\Yii::$app->params[Checksum::PARAM_VENDOR])) {
            return;
        }
        
        try {
            static::getCurrentVendorChecksum();
        } catch (\Throwable $e) {
            \Yii::error('Не удалось пересчитать хеш-сумму папки vendor' . PHP_EOL . $e->getMessage(), 'CHECKSUM');
        }
    }

    




    public static function calculateChecksum(string $directory): string
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("Аргумент должен указывать на директорию");
        }

        $files = array();
        $names = array();
        $dir = dir($directory);
        $ignore_dirs = static::getIgnoreDirList();
        $ignore_files = ['.', '..'];
        
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
                $files[] = static::calculateChecksum($fullpath);
            } else {
                $files[] = static::getFileHash($fullpath);
            }

            $names[] = static::unifyFilename($fullpath);
        }

        sort($files);
        sort($names);
        
        $dir->close();

        return static::getStringHash(implode('', $files) . implode('', $names));
    }
    
    public static function getVendorPath(): string
    {
        return \Yii::getAlias('@base') . DIRECTORY_SEPARATOR . 'vendor';
    }
    
    




    public static function getHashOfChecksum(string $checksum): string
    {
        return md5($checksum);
    }
    
    protected static function needToCheckHashsum(): bool
    {
        return getenv('VERIFY_DEPENDENCIES_CHECKSUM') !== 'false';
    }
    
    



    public static function getIgnoreDirList(): array
    {
        $vendor_path = static::getVendorPath();
        
        return [
            $vendor_path . DIRECTORY_SEPARATOR . 'autoload.php',
            $vendor_path . DIRECTORY_SEPARATOR . 'composer',
            $vendor_path . DIRECTORY_SEPARATOR . 'bin',
            $vendor_path . DIRECTORY_SEPARATOR . 'yiisoft' . DIRECTORY_SEPARATOR . 'extensions.php',
        ];
    }

    



    public static function getIgnoreFileList(): array
    {
        return ['.', '..'];
    }
    
    



    public static function getFileHash(string $path)
    {
        return md5_file($path);
    }
    
    



    protected static function getStringHash(string $string): string
    {
        return md5($string);
    }

    public static function unifyFilename($fullpath)
    {
        $relative_path = substr($fullpath, strlen(\Yii::getAlias('@base')));
        $relative_path = str_replace('\\', '/', $relative_path); 
        return $relative_path;
    }
}
