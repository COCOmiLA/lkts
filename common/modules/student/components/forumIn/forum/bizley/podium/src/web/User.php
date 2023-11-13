<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\web;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\rbac\CheckAccessInterface;
use yii\web\User as YiiUser;







class User extends YiiUser
{
    



    protected function getAccessChecker()
    {
        return Podium::getInstance()->rbac;
    }
}