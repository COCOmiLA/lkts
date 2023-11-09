<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\filters;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;








class InstallRule extends PodiumRoleRule
{
    


    public $allow = false;

    


    public function init()
    {
        parent::init();
        $this->matchCallback = function () {
            return !Podium::getInstance()->getInstalled();
        };
        $this->denyCallback = function () {
            return Yii::$app->response->redirect([Podium::getInstance()->prepareRoute('install/run')]);
        };
    }
}
