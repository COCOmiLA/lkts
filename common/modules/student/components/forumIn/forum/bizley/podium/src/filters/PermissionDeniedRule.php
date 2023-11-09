<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\filters;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;








class PermissionDeniedRule extends PodiumRoleRule
{
    


    public $allow = false;

    


    public $perm;

    


    public $redirect;

    


    public function init()
    {
        parent::init();
        $this->matchCallback = function () {
            return !User::can($this->perm);
        };
        $this->denyCallback = function () {
            Yii::$app->session->addFlash('danger', Yii::t('podium/flash', 'You are not allowed to perform this action.'), true);
            return Yii::$app->response->redirect([Podium::getInstance()->prepareRoute($this->redirect)]);
        };
    }
}
