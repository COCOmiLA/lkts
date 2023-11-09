<?php

use common\components\Migration\MigrationWithDefaultOptions;
use yii\helpers\FileHelper;




class m201207_074529_convert__dotEnv_ extends MigrationWithDefaultOptions
{
    


    public function safeUp()
    {
        $pathToBackups = FileHelper::normalizePath(__DIR__ . '../../../../backend/backups');
        if (!file_exists($pathToBackups)) {
            if (!mkdir($pathToBackups, 0777, true)) {
                echo "Ошибка. Невозможно создать директорию по пути: '{$pathToBackups}'\n";
                return false;
            }
        }
        $time = date('Y_m_d__H_i_s');
        $oldFile = FileHelper::normalizePath(__DIR__ . '../../../../.env');
        $newFile = FileHelper::normalizePath(__DIR__ . "../../../../backend/backups/.env.backup__{$time}");

        try {
            $isCopied = copy($oldFile, $newFile);
        } catch (\Throwable $e) {
            echo "Критическая ошибка копирования, по причине: '{$e->getMessage()}'\n\n";
            $isCopied = false;
        }
        if ($isCopied) {
            $dotEnv = file_get_contents($oldFile);
            if (!empty($dotEnv)) {
                $dotEnv = preg_replace('~(?:\G(?!\A)|")[^"\s]*\K(?:\s|"(*SKIP)(*F))~', '®', $dotEnv);
                $dotEnv = str_replace(' ', '', $dotEnv);
                $dotEnv = str_replace('®', ' ', $dotEnv);
                $isWrithed = file_put_contents($oldFile, $dotEnv, LOCK_EX);
                if ($isWrithed !== false) {
                    return true;
                }
                echo "Ошибка. Невозможно записать изменения в '{$oldFile}'\n";
                return false;
            }
            echo "Ошибка. Файли '{$oldFile}' пустой\n";
            return false;
        }
        echo "Ошибка копирования '{$oldFile}' в '{$newFile}'\n";
        return false;
    }

    


    public function safeDown()
    {
        echo "m201207_074529_convert__dotEnv_ cannot be reverted.\n";
    }
}
