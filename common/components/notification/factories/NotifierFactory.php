<?php

namespace common\components\notification\factories;

use common\components\notification\ChatNotifier;
use common\components\notification\EmailNotifier;
use common\components\notification\ICanNotify;
use common\components\notification\PopupNotifier;
use common\models\notification\Notification;
use common\models\notification\NotificationType;
use Yii;
use yii\base\InvalidArgumentException;

class NotifierFactory implements INotifierFactory
{
    protected static $map = [
        NotificationType::TYPE_EMAIL => EmailNotifier::class,
        NotificationType::TYPE_POPUP => PopupNotifier::class
    ];

    protected static function create(string $type, array $params = []): ICanNotify
    {
        if (!isset(static::$map[$type])) {
            throw new InvalidArgumentException("Нет обработчика типа $type.");
        }

        return Yii::createObject(static::$map[$type], $params);
    }

    



    public function getNotifiers(array $types): array
    {
        $notifiers = [];
        foreach ($types as $type) {
            $notifiers[] = $this->create($type);
        }

        return $notifiers;
    }

    




    public static function getChatNotifier(int $senderId): ICanNotify
    {
        return new ChatNotifier(new PopupNotifier([
            'sender_id' => $senderId,
            'category' => Notification::CATEGORY_CHAT,
        ]));
    }
}
