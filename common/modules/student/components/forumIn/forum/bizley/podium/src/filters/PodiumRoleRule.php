<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\filters;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use yii\filters\AccessRule;
use yii\web\User as YiiUser;








class PodiumRoleRule extends AccessRule
{
    



    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }
        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            } elseif (User::can($role)) {
                return true;
            }
        }

        return false;
    }
}
