<?php

namespace common\components\UserReferenceTypeManager;

use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\soapException;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\EmptyCheck;
use common\models\interfaces\IReferenceCanUpdate;
use common\models\ModelFrom1CByOData;
use common\models\User;
use yii\base\UserException;





class UserReferenceTypeManager
{
    










    public static function GetProcessedUserReferenceType(User $user)
    {
        $userReference = null;
        if (empty($user->user_ref_id)) {
            $userReference = UserReferenceTypeManager::getUserReferenceFrom1C($user);
            if (isset($userReference)) {
                $user->user_ref_id = $userReference->id;
                $user->save(true, ['user_ref_id']);
            }
        }

        return ReferenceTypeManager::GetReference(
            $user,
            'userRef',
            StoredUserReferenceType::getReferenceClassName(),
            !EmptyCheck::isEmpty($user->guid)
        );
    }

    










    public static function getUserReferenceFrom1C(User $user)
    {
        return static::getUserReferenceFrom1CByGuid($user->guid);
    }

    public static function getRawUserReferenceFrom1CByGuid(?string $guid, $param_type = 'Код')
    {
        $referenceType = ReferenceTypeManager::GetReferenceTypeFrom1C($guid, StoredUserReferenceType::getReferenceClassToFill(), $param_type);
        if (is_null($referenceType) || ReferenceTypeManager::isReferenceTypeEmpty($referenceType->toObject())) {
            return null;
        }

        return $referenceType->toObject();
    }

    public static function getUserReferenceFrom1CByGuid(?string $guid, $param_type = 'Код')
    {
        $referenceType = UserReferenceTypeManager::getRawUserReferenceFrom1CByGuid($guid, $param_type);
        if (!$referenceType) {
            return null;
        }
        return ReferenceTypeManager::GetOrCreateReference(StoredUserReferenceType::class, $referenceType);
    }

    




    public static function GetUserFromEntrant(string $userGuid, StoredUserReferenceType $userRef = null): ?User
    {
        $user = null;

        if (is_null($userRef)) {
            if (!empty($userGuid)) {
                $user = User::findOne([
                    'system_uuid' => $userGuid
                ]);
            }
        } else {
            $user = User::findOne([
                'user_ref_id' => $userRef->id
            ]);
            if (is_null($user)) {
                $user = User::findOne([
                    'system_uuid' => $userGuid
                ]);

                if ($user) {
                    $user->user_ref_id = $userRef->id;
                    $user->guid = $userRef->reference_id;
                    $user->save(true, ['guid', 'user_ref_id']);
                }
            }
        }
        return $user;
    }

    



    public static function IsUserRefDataVersionOutdated(User $user)
    {
        if (!$user->userRef) {
            return false;
        }
        $old_data_version = $user->userRef->reference_data_version;
        $rawUserRef = UserReferenceTypeManager::getRawUserReferenceFrom1CByGuid($user->userRef->reference_uid, 'Идентификатор');
        if (!$rawUserRef) {
            return false;
        }

        return $old_data_version != $rawUserRef->ReferenceDataVersion;
    }
}
