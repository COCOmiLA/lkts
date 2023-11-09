<?php




namespace common\actions;

use common\components\secureUrlManager\SecureUrlManager;
use Yii;
use yii\base\Action;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;
























class SetLocaleAction extends Action
{
    


    public $languages;

    


    public $localeCookieName = '_locale';

    


    public $cookieExpire;

    


    public $cookieDomain;

    


    public $callback;


    



    public function run($language)
    {
        if (!is_array($this->languages) || !in_array($language, $this->languages, true)) {
            throw new NotFoundHttpException('Указан не поддерживаемый язык');
        }
        $cookie = new Cookie([
            'name' => $this->localeCookieName,
            'value' => $language,
            'expire' => $this->cookieExpire ?: time() + 60 * 60 * 24 * 365,
            'domain' => $this->cookieDomain ?: '',
            'httpOnly' => true,
            'secure' => SecureUrlManager::isHttpsEnabled()
        ]);
        Yii::$app->getResponse()->getCookies()->add($cookie);
        if ($this->callback instanceof \Closure) {
            return call_user_func_array($this->callback, [
                $this,
                $language
            ]);
        }
        return Yii::$app->response->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
    }
}
