<?php

namespace common\models\logs;

use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yii\log\LogRuntimeException;

class PortalDbTarget extends \yii\log\DbTarget
{
    



    public function export()
    {
        if ($this->db->getTransaction()) {
            
            
            $this->db = clone $this->db;
        }

        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]])
                VALUES (:level, :category, :log_time, :prefix, :message)";
        $command = $this->db->createCommand($sql);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                
                if ($text instanceof \Throwable) {
                    $text = (string)$text;
                } else {
                    $text = VarDumper::export($text);
                }
            }
            if ($command->bindValues([
                    ':level' => $level,
                    ':category' => $category,
                    ':log_time' => $timestamp,
                    ':prefix' => $this->getMessagePrefix($message),
                    ':message' => $this->prepareMessageText($text),
                ])->execute() > 0) {
                continue;
            }
            throw new LogRuntimeException('Unable to export log through database!');
        }
    }

    protected function prepareMessageText(string $text): string
    {
        
        return StringHelper::truncate($text, 16777214, '');
    }
}