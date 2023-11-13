<?php

namespace api\modules\moderator\modules\v1\DTO\EntrantPackage;


use api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO;
use api\modules\moderator\modules\v1\models\EntrantApplication\EntrantApplication;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\FullApplicationPackageBuilder;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotFindReferenceException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredUserReferenceType;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\IDraftable;
use yii\web\NotFoundHttpException;






class EntrantPackageDTO extends BaseXMLSerializedDTO
{

    public function serialize()
    {
        return null;
    }

    public function updateApplication(EntrantApplication $app)
    {
        return (new FullApplicationPackageBuilder($app))
            ->setUpdateSentAt(true)
            ->updateUserRefByFullPackage($this->getSerializedData())
            ->update($this->getSerializedData());
    }

    








    public function getApplication(User $user): EntrantApplication
    {
        $referenceCampaign = ReferenceTypeManager::GetOrCreateReference(StoredAdmissionCampaignReferenceType::class, $this->getSerializedData()->Entrant->CampaignRef);
        
        $application = EntrantApplication::find()
            ->innerJoinWith(['type.campaign.referenceType ref'])
            ->where(['user_id' => $user->id])
            ->andWhere([
                'ref.reference_uid' => $referenceCampaign->reference_uid
            ])
            ->andWhere([
                BachelorApplication::tableName() . '.draft_status' => IDraftable::DRAFT_STATUS_SENT,
                BachelorApplication::tableName() . '.archive' => false,
            ])
            ->one();

        if (is_null($application)) {
            throw new NotFoundHttpException('В базах портала не найдено подходящее заявление.');
        }

        return $application;
    }

    





    public function getUserReferenceType(): ?StoredUserReferenceType
    {
        if (ReferenceTypeManager::isReferenceTypeEmpty($this->getSerializedData()->Entrant->EntrantRef)) {
            return null;
        }
        return ReferenceTypeManager::GetOrCreateReference(StoredUserReferenceType::class, $this->getSerializedData()->Entrant->EntrantRef);
    }

    





    public function getUser(): User
    {
        $user = UserReferenceTypeManager::GetUserFromEntrant($this->getSerializedData()->Entrant->EntrantPortalGUID, $this->getUserReferenceType());
        
        if (is_null($user)) {
            throw new NotFoundHttpException('Пользователь не найден в системе портала');
        }
        return $user;
    }
}