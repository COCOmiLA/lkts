<?php

namespace common\components\notification;

use common\modules\abiturient\models\File;

class ChatNotifier extends BaseNotifier
{
    
    private $notifer;
    
    public function __construct(ICanNotify $notifier, $config = [])
    {
        $this->notifer = $notifier;
        parent::__construct($config);
    }
    
    






    public function send(string $title, string $body, array $user_ids, array $files = []): array
    {
        return $this->notifer->send($title, $body, $user_ids, $files);
    }
}
