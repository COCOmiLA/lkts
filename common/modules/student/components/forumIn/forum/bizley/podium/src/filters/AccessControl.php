<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\filters;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\filters\AccessControl as YiiAccessControl;







class AccessControl extends YiiAccessControl
{
    




    public $ruleConfig = ['class' => 'common\modules\student\components\forumIn\forum\bizley\podium\src\filters\PodiumRoleRule'];

    


    public function init()
    {
        $this->user = Podium::getInstance()->user;
        parent::init();
    }
}
