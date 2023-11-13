<?php

namespace common\components\notifier;

use common\models\User;
use common\models\UserRegistrationConfirmToken;
use Yii;
use yii\helpers\ArrayHelper;
use yii\mail\MessageInterface;

class notifier extends \yii\base\Component
{
    public function notifyAboutRegister($user_id, $password)
    {
        $user = User::findOne($user_id);

        $header = Yii::t(
            'abiturient/notifier/registred',
            'Заголовок для письма регистрации в менеджере оповещений: `Регистрация`'
        );
        $this->sendTemplateMail($user->email, "{$header} | " . Yii::$app->name, "../../common/components/notifier/views/registred", [
            'fio' => $user->getPublicIdentity(),
            'login' => $user->email,
            'password' => $password,
        ]);
    }

    public function notifyAboutApplyApplication($user_id, $comment = null)
    {
        $user = User::findOne($user_id);

        $header = Yii::t(
            'abiturient/notifier/application-applyed',
            'Заголовок для письма о принятии заявления в менеджере оповещений: `Заявление принято`'
        );
        $this->sendTemplateMail($user->email, "{$header} | " . Yii::$app->name, "../../common/components/notifier/views/application-applyed", [
            'fio' => $user->getPublicIdentity(),
            'comment' => $comment
        ]);
    }

    public function notifyAboutSendApplication($user_id)
    {
        $user = User::findOne($user_id);

        $header = Yii::t(
            'abiturient/notifier/application-sended',
            'Заголовок для письма при отправке заявления на проверку в менеджере оповещений: `Заявление передано на рассмотрение модератору`'
        );
        $this->sendTemplateMail($user->email, "{$header} | " . Yii::$app->name, "../../common/components/notifier/views/application-sended", [
            'fio' => $user->getPublicIdentity(),
        ]);
    }

    public function notifyAboutRejectApplication($user_id)
    {
        $user = User::findOne($user_id);

        $header = Yii::t(
            'abiturient/notifier/application-rejected',
            'Заголовок для письма при отклонения заявления системой в менеджере оповещений: `Заявление отклонено системой`'
        );
        $this->sendTemplateMail($user->email, "{$header} | " . Yii::$app->name, "../../common/components/notifier/views/application-rejected", [
            'fio' => $user->getPublicIdentity(),
        ]);
    }

    public function notifyAboutDeclineApplication($user_id, $comment)
    {
        $user = User::findOne($user_id);

        $header = Yii::t(
            'abiturient/notifier/application-declined',
            'Заголовок для письма при отклонения заявления модератором в менеджере оповещений: `Заявление отклонено`'
        );
        $this->sendTemplateMail($user->email, "{$header} | " . Yii::$app->name, "../../common/components/notifier/views/application-declined", [
            'fio' => $user->getPublicIdentity(),
            'comment' => $comment,
        ]);
    }

    public function notifyAboutChangeSpecialities($userId, $campaignName)
    {
        $user = User::findOne($userId);

        $header = Yii::t(
            'abiturient/notifier/change-specialities',
            'Заголовок для письма при изменении НП заявления модератором в менеджере оповещений: `В направления подготовки были внесены изменения`'
        );
        $this->sendTemplateMail(
            $user->email,
            "{$header} | " . Yii::$app->name,
            "../../common/components/notifier/views/change-specialities",
            [
                'fio' => $user->getPublicIdentity(),
                'campaignName' => $campaignName,
            ]
        );
    }

    






    public function notifyAboutEmailConfirmation(User $user, UserRegistrationConfirmToken $token)
    {
        $header = Yii::t(
            'abiturient/notifier/email-confirm',
            'Заголовок для письма подтверждения электронной почты в менеджере оповещений: `Подтвердите свой email`'
        );
        return $this->sendTemplateMail($user->email, "{$header} | " . Yii::$app->name, "../../common/components/notifier/views/email-confirm", [
            'user' => $user,
            'token' => $token,
            'ttl' => Yii::$app->configurationManager->getSignupEmailTokenTTL()
        ]);
    }

    public function sendTemplateMail($to, $subject, $view_name, $params): bool
    {
        
        if (empty(getenv('MAIL_HOST')) or empty(getenv('MAIL_USERNAME'))) {
            return false;
        }

        try {
            $this->initMessageBuilder(Yii::$app->mailer->compose($view_name, $params), $subject)
                ->setTo($to)
                ->send();
        } catch (\Throwable $e) {
            Yii::error("Ошибка отправки почты: ({$e->getMessage()}) " . PHP_EOL . print_r(
                [
                    'view_name' => $view_name,
                    'to' => $to,
                    'subject' => $subject,
                    'params' => $params
                ],
                true
            ));
            return false;
        }
        return true;
    }

    public function sendMail($to, $subject, $text)
    {
        $this->initMessageBuilder(Yii::$app->mailer->compose(), $subject)
            ->setTo($to)
            ->setTextBody($text)
            ->send();
    }

    public function initMessageBuilder(MessageInterface $composed, $subject): MessageInterface
    {
        
        return $composed
            ->setFrom(ArrayHelper::getValue(Yii::$app->mailer, 'messageConfig.from'))
            ->setSubject($subject);
    }
}
