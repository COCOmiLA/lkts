<?php

namespace common\components\notification;

use common\components\notifier\notifier;
use common\models\User;
use common\modules\abiturient\models\File;
use Throwable;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\VarDumper;

final class EmailNotifier extends BaseNotifier
{
    
    protected $notifier;

    public function init()
    {
        parent::init();
        $this->notifier = Yii::$app->notifier;
    }
    
    






    public function send(string $title, string $body, array $user_ids, array $files = []): array
    {
        $count_sent = 0;
        
        $message = $this->notifier->initMessageBuilder(Yii::$app->mailer->compose(), $title);
        
        foreach ($files as $file) {
            if (!$file instanceof File) {
                throw new InvalidArgumentException();
            }
            $message->attach($file->getFilePath());
        }
        
        foreach (User::find()->select(['id','email'])->andWhere(['id' => $user_ids])->batch($this->batch_size) as $users) {
            foreach ($users as $user) {
                try {
                    if ($message->setTextBody($body)->setTo($user->email)->send()) {
                        $count_sent++;
                    }
                } catch (Throwable $e) {
                    Yii::error("Ошибка отправки почты: ({$e->getMessage()}) " . PHP_EOL . VarDumper::dumpAsString([
                        'to' => $user->email,
                        'subject' => $title
                    ]), 'EMAIL_NOTIFIER');
                }
            }
        }
        
        Yii::info("Отправлено {$count_sent} писем", 'EMAIL_NOTIFIER');
        
        return [];
    }
}
