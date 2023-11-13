<?php

namespace common\components\behaviors\emailConfirmBehavior;

use common\models\User;
use Yii;
use yii\base\Behavior;
use yii\base\Module;
use yii\helpers\Url;

class EmailConfirmBehavior extends Behavior
{
    


    public $user;

    public function events()
    {
        $resultArray = [];
        if (Yii::$app->configurationManager->getSignupEmailConfirm()) {
            $resultArray[Module::EVENT_BEFORE_ACTION] = 'checkUserEmailConfirm';
        }
        return $resultArray;
    }

    public function checkUserEmailConfirm()
    {
        $is_moder = $this->user->isInRole(\common\models\User::ROLE_MANAGER);
        $is_admin = $this->user->isInRole(\common\models\User::ROLE_ADMINISTRATOR);
        if (!($is_moder || $is_admin) && !$this->user->isRegistrationConfirmed()) {
            Yii::$app->response->redirect(Url::toRoute('/user/sign-in/confirm-email'), 301)->send();
        }
    }
}