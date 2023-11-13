<?php

namespace common\modules\abiturient\models\helpers;

use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;

class UserInfoRenderHelper
{
    




    public static function getUserDescription(array $user_data, StoredUserReferenceType $userRef): string
    {
        $result = $userRef->reference_name;
        if (isset($user_data['LastName'])) {
            $result = "{$user_data['LastName']} {$user_data['FirstName']} {$user_data['SecondName']} - {$user_data['Birthdate']} - паспорт: {$user_data['PassportSeries']} {$user_data['PassportNumber']}";
        }
        if (isset($user_data['SNILS'])) {
            $result = "{$result} - СНИЛС: {$user_data['SNILS']}";
        }

        if (isset($user_data['DeletionMark']) && $user_data['DeletionMark']) {
            $result = trim("{$result} <strong>помечен на удаление</strong>");
        }
        return $result;
    }
}