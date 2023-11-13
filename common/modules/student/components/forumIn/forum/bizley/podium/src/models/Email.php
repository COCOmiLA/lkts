<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models;

use common\modules\student\components\forumIn\forum\bizley\podium\src\log\Log;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\db\EmailActiveRecord;
use Exception;
use Yii;







class Email extends EmailActiveRecord
{
    







    public static function queue($address, $subject, $content, $userId = null)
    {
        try {
            $email = new static;
            $email->user_id = $userId;
            $email->email = $address;
            $email->subject = $subject;
            $email->content = $content;
            $email->status = self::STATUS_PENDING;
            $email->attempt = 0;

            Yii::$app->mailer->compose()
                ->setTo($address)
                ->setSubject($subject)
                ->setTextBody($content)
                ->send();

            return $email->save();
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }

        return false;
    }
}
