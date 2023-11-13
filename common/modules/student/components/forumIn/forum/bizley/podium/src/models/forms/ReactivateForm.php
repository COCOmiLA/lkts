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










class ReactivateForm extends Model
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
        $user = $this->getUser(User::STATUS_REGISTERED);
        if (empty($user)) {
            return [
                true,
                Yii::t('podium/flash', 'Sorry! We can not find the account with that user name or e-mail address.'),
                false
            ];
        }
        $user->scenario = 'token';
        $user->generateActivationToken();
        if (!$user->save()) {
            return [true, null, false];
        }
        if (empty($user->email)) {
            return [
                true,
                Yii::t('podium/flash', 'Sorry! There is no e-mail address saved with your account. Contact administrator about reactivating.'),
                true
            ];
        }
        if (!$this->sendReactivationEmail($user)) {
            return [
                true,
                Yii::t('podium/flash', 'Sorry! There was some error while sending you the account activation link. Contact administrator about this problem.'),
                true
            ];
        }
        return [
            false,
            Yii::t('podium/flash', 'The account activation link has been sent to your e-mail address.'),
            true
        ];
    }

    





    protected function sendReactivationEmail(User $user)
    {
        $forum = Podium::getInstance()->podiumConfig->get('name');
        $email = Content::fill(Content::EMAIL_REACTIVATION);
        if ($email !== false) {
            $link = Url::to(['account/activate', 'token' => $user->activation_token], true);
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
