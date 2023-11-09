<?php

namespace api\modules\moderator\modules\v1\DTO\GetEntrantApplication;

use api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO;
use api\modules\moderator\modules\v1\DTO\ReferenceType\CampaignRefDTO;
use api\modules\moderator\modules\v1\DTO\ReferenceType\UserRefDTO;
use api\modules\moderator\modules\v1\models\EntrantApplication\EntrantApplication;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotFindReferenceException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\UserReferenceTypeManager\UserReferenceTypeManager;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\IDraftable;
use yii\web\NotFoundHttpException;







class PortalEntrantApplicationDTO extends BaseXMLSerializedDTO
{

    


    protected $CampaignRef;

    


    protected $EntrantRef;

    


    protected $EntrantPortalGUID;

    


    public function getPropertyCampaignRef(): CampaignRefDTO
    {
        return $this->CampaignRef;
    }

    


    public function getPropertyEntrantPortalGUID(): string
    {
        return $this->EntrantPortalGUID;
    }

    public function getPropertyEntrantRef(): UserRefDTO
    {
        return $this->EntrantRef;
    }

    








    public function getApplication($draft_statuses = [IDraftable::DRAFT_STATUS_SENT]): EntrantApplication
    {
        $user = UserReferenceTypeManager::GetUserFromEntrant($this->getPropertyEntrantPortalGUID(), $this->getPropertyEntrantRef()->getStoredReferenceType());

        if (is_null($user)) {
            throw new NotFoundHttpException('Пользователь не найден в системе портала');
        }

        
        $campaignRef = $this->getPropertyCampaignRef()->getStoredReferenceType();
        
        $application = EntrantApplication::find()
            ->innerJoinWith(['type.campaign.referenceType ref'])
            ->where(['user_id' => $user->id])
            ->andWhere([
                'ref.reference_uid' => $campaignRef->reference_uid
            ])
            ->andWhere([
                BachelorApplication::tableName() . '.draft_status' => $draft_statuses,
                BachelorApplication::tableName() . '.archive' => false,
            ])
            ->one();

        if (is_null($application)) {
            throw new NotFoundHttpException('В базах портала не найдено подходящее заявление.');
        }
        return $application;
    }

}