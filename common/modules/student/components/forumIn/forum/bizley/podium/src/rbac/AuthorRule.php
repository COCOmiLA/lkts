<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\rbac;

use yii\rbac\Rule;







class AuthorRule extends Rule
{
    public $name = 'isPodiumAuthor';

    





    public function execute($user, $item, $params)
    {
        return isset($params['post']) ? $params['post']->author_id == $user : false;
    }
}
