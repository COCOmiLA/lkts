<?php
namespace api\modules\moderator\modules\v1\DTO\ReferenceType;


use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;

class CampaignRefDTO extends BaseReferenceTypeDTO
{
    public function getStoredReferenceTypeClass(): string
    {
        return StoredAdmissionCampaignReferenceType::class;
    }
}