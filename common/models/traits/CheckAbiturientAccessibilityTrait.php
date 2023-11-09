<?php

namespace common\models\traits;

use common\models\errors\AbiturientAccessDenied;
use common\models\User;

trait CheckAbiturientAccessibilityTrait
{
    


    public static function getUserLinkColumn(): string
    {
        return 'user_id';
    }

    




    public static function isAbiturient(?User $currentUser): bool
    {
        if (!$currentUser) {
            return false;
        }

        return $currentUser->isInRole(User::ROLE_ABITURIENT);
    }

    







    public static function checkAccessibility(?User $currentUser, int $tablePrimaryKey): void
    {
        if (!static::isAbiturient($currentUser)) {
            return;
        }

        $tableRow = static::findOne($tablePrimaryKey);
        if (!$tableRow) {
            return;
        }

        $userLinkColumn = self::getUserLinkColumn();
        if ($currentUser->id == $tableRow->{$userLinkColumn}) {
            return;
        }

        throw new AbiturientAccessDenied($tableRow, $currentUser);
    }
}
