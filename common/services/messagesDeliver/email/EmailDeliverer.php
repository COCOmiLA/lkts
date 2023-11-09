<?php

namespace common\services\messagesDeliver\email;

use common\components\notifier\notifier;
use common\models\User;
use common\services\messagesDeliver\ICanDeliverMessage;
use Throwable;
use Yii;
use yii\helpers\VarDumper;
use yii\swiftmailer\Mailer;

class EmailDeliverer implements ICanDeliverMessage
{
    private EmailRecipient $recipient;
    private notifier $notifier;
    private Mailer $mailer;

    public function __construct(notifier $notifier, Mailer $mailer)
    {
        $this->notifier = $notifier;
        $this->mailer = $mailer;
    }

    



    public function setRecipient(User $recipient): EmailDeliverer
    {
        $this->recipient = new EmailRecipient($recipient);
        return $this;
    }

    


    public function deliverMessage(string $title, string $message): array
    {
        $email = $this->recipient->getRecipientAddress();
        if (empty($email)) {
            return [false, 'Получатель не указал email'];
        }
        $delivery = $this->notifier->initMessageBuilder($this->mailer->compose(), $title);
        try {
            if (!$delivery->setTextBody($message)->setTo($email)->send()) {
                throw new \yii\web\ServerErrorHttpException('Не удалось отправить сообщение');
            }
            return [true, null];
        } catch (Throwable $e) {
            Yii::error("Ошибка отправки почты: ({$e->getMessage()}) " . PHP_EOL . VarDumper::dumpAsString([
                    'to' => $email,
                    'subject' => $title
                ]), 'EMAIL_NOTIFIER');
            return [false, $e->getMessage()];
        }
    }
}