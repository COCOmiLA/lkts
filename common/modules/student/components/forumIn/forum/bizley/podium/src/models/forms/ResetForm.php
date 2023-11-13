<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\forms;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Content;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Email;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Url;










class ResetForm extends Model
{
    


    public $username;

    


    public function rules()
    {
        return [['username', 'required']];
    }

    private $_user = false;

    




    public function getUser($status = User::STATUS_ACTIVE)
    {
        if ($this->_user === false) {
            $this->_user = User::findByKeyfield($this->username, $status);
        }
        return $this->_user;
    }

    



    public function run()
    {
        $user = $this->user;
        if (empty($user)) {
            return [
                true,
                Yii::t('podium/flash', 'Sorry! We can not find the account with that user name or e-mail address.'),
                false
            ];
        }
        $user->scenario = 'token';
        $user->generatePasswordResetToken();
        if (!$user->save()) {
            return [true, null, false];
        }
        if (empty($user->email)) {
            return [
                true,
                Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about resetting password.'),
                true
            ];
        }
        if (!$this->sendResetEmail($user)) {
            return [
                true,
                Yii::t('podium/flash', 'Sorry! There was some error while sending you the password reset link. Contact administrator about this problem.'),
                true
            ];
        }
        return [
            false,
            Yii::t('podium/flash', 'The password reset link has been sent to your e-mail address.'),
            true
        ];
    }

    





    protected function sendResetEmail(User $user)
    {
        $forum = Podium::getInstance()->podiumConfig->get('name');
        $email = Content::fill(Content::EMAIL_PASSWORD);
        if ($email !== false) {
            $link = Url::to(['account/password', 'token' => $user->password_reset_token], true);
            return Email::queue(
                $user->email,
                str_replace('{forum}', $forum, $email->topic),
                str_replace('{forum}', $forum, str_replace('{link}',
                    Html::a($link, $link), $email->content)),
                !empty($user->id) ? $user->id : null
            );
        }
        return false;
    }
}
