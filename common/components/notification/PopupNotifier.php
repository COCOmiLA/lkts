<?php

namespace common\components\notification;

use common\models\errors\RecordNotValid;
use common\models\notification\Notification;
use common\models\notification\NotificationAttachment;
use common\models\notification\NotificationContent;
use common\models\User;
use common\modules\abiturient\models\File;
use Throwable;
use Yii;

final class PopupNotifier extends BaseNotifier
{
    






    public function send(string $title, string $body, array $user_ids, array $files = []): array
    {
        $result = [];

        $notification_content = new NotificationContent();
        $notification_content->title = $title;
        $notification_content->body = $body;
        if (!$notification_content->save()) {
            throw new RecordNotValid($notification_content);
        }

        foreach (User::find()->andWhere(['id' => $user_ids])->batch($this->batch_size) as $users) {
            foreach ($users as $user) {
                try {
                    $notification = new Notification();
                    $notification->category = $this->category;
                    $notification->receiver_id = $user->id;
                    $notification->notification_content_id = $notification_content->id;
                    $notification->sender_id = $this->sender_id;
                    if (!$notification->save()) {
                        throw new RecordNotValid($notification);
                    }
                    $this->linkFiles($notification, $files);
                    $result[] = $notification->id;
                } catch (Throwable $e) {
                    Yii::error($e->getMessage(), 'POPUP_NOTIFIER');
                }
            }
        }

        return $result;
    }

    




    protected function linkFiles($notification, array $stored_files)
    {
        foreach ($stored_files as $stored_file) {
            $attachment = new NotificationAttachment();
            $attachment->notification_id = $notification->id;
            if (!$attachment->save()) {
                throw new RecordNotValid($attachment);
            }
            $attachment->LinkFile($stored_file);
        }
    }
}
