<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\rbac;

use yii\rbac\Rule;







class ModeratorRule extends Rule
{
    public $name = 'isPodiumModerator';

    





    public function execute($user, $item, $params)
    {
        return isset($params['item']) ? $params['item']->isMod() : false;
    }
}
