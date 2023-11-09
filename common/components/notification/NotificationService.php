<?php

namespace common\components\notification;

use common\components\notification\factories\INotifierFactory;
use common\models\notification\NotificationAttachment;
use common\modules\abiturient\models\File;
use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\web\UploadedFile;

class NotificationService extends BaseObject
{
    
    protected $notifier_factory;

    



    public function __construct(INotifierFactory $factory, $config = [])
    {
        $this->notifier_factory = $factory;
        parent::__construct($config);
    }
    
    







    public function send(array $types, string $title, string $body, array $receiver_ids, array $files = [])
    {
        $stored_files = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $stored_files[] = File::GetOrCreateByTempFile(
                    (new NotificationAttachment())->getPathToStoreFiles(), 
                    $file);
            } elseif ($file instanceof File) {
                $stored_files[] = $file;
            } else {
                throw new InvalidArgumentException();
            }
        }
        
        foreach ($this->notifier_factory->getNotifiers($types) as $notifier) {
            try {
                $notifier->send($title, $body, $receiver_ids, $stored_files);
            } catch (Throwable $e) {
                Yii::error($e->getMessage(), 'NOTIFICATION_SERVICE');
            }
        }
    }
}
