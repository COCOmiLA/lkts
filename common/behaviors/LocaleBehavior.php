<?php

namespace common\behaviors;

use common\models\EmptyCheck;
use Yii;
use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\web\Application;





class LocaleBehavior extends Behavior
{
    


    public $cookieName = '_locale';

    


    public $enablePreferredLanguage = false;

    


    public function events()
    {
        return [
            Application::EVENT_BEFORE_REQUEST => 'beforeRequest'
        ];
    }

    


    public function beforeRequest()
    {
        if (
            Yii::$app->getRequest()->getCookies()->has($this->cookieName)
        ) {
            $userLocale = Yii::$app->getRequest()->getCookies()->getValue($this->cookieName);
        } else {
            $userLocale = Yii::$app->language;

            $user_profile_locale = ArrayHelper::getValue(Yii::$app->user, 'identity.userProfile.locale');
            if (!EmptyCheck::isEmpty($user_profile_locale) && in_array($user_profile_locale, $this->getAvailableLocales())) {
                $userLocale = Yii::$app->user->identity->userProfile->locale;
            } elseif ($this->enablePreferredLanguage) {
                $userLocale = Yii::$app->request->getPreferredLanguage($this->getAvailableLocales());
            }
        }
        Yii::$app->language = $userLocale;
    }

    


    protected function getAvailableLocales()
    {
        return Yii::$app->localizationManager->getAvailableLocales();
    }
}
