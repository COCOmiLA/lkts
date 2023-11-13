<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\log;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;
use yii\helpers\VarDumper;
use yii\log\DbTarget as YiiDbTarget;
use yii\web\Request;








class DbTarget extends YiiDbTarget
{
    


    public function export()
    {
        $tableName = Podium::getInstance()->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[ip]], [[message]], [[model]], [[user]])
                VALUES (:level, :category, :log_time, :ip, :message, :model, :user)";
        $command = Podium::getInstance()->db->createCommand($sql);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            $extracted = [
                'msg'   => '',
                'model' => null,
            ];
            if (is_array($text) && (isset($text['msg']) || isset($text['model']))) {
                if (isset($text['msg'])) {
                    if (!is_string($text['msg'])) {
                        $extracted['msg'] = VarDumper::export($text['msg']);
                    } else {
                        $extracted['msg'] = $text['msg'];
                    }
                }
                if (isset($text['model'])) {
                    $extracted['model'] = $text['model'];
                }
            } elseif (is_string($text)) {
                $extracted['msg'] = $text;
            } else {
                $extracted['msg'] = VarDumper::export($text);
            }
            if (substr($category, 0, 14) == 'common\modules\student\components\forumIn\forum\bizley\podium\src\\') {
                $category = substr($category, 14);
            }
            $request = Yii::$app->getRequest();
            $command->bindValues([
                ':level'    => $level,
                ':category' => $category,
                ':log_time' => $timestamp,
                ':ip'       => $request instanceof Request ? $request->getUserIP() : null,
                ':message'  => $extracted['msg'],
                ':model'    => $extracted['model'],
                ':user'     => Log::blame(),
            ])->execute();
        }
    }
}
