<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\filters;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;








class LoginRequiredRule extends PodiumRoleRule
{
    


    public $allow = false;

    


    public $roles = ['?'];

    


    public $message;

    


    public $type = 'warning';

    


    public function init()
    {
        parent::init();
        $this->denyCallback = function () {
            Yii::$app->session->addFlash($this->type, $this->message, true);
            return Yii::$app->response->redirect([Podium::getInstance()->prepareRoute('account/login')]);
        };
    }
}
