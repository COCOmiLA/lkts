<?php


namespace api\modules\moderator\modules\v1\DTO\VerifyLastMasterServerHistoryDTO;


use api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO;
use api\modules\moderator\modules\v1\DTO\ReferenceType\CampaignRefDTO;

class VerifyLastMasterServerHistoryDTO extends BaseXMLSerializedDTO
{

    


    protected $CampaignRef;

    


    protected $Entrants;

    


    public function getPropertyCampaignRef(): CampaignRefDTO
    {
        return $this->CampaignRef;
    }

    


    public function getPropertyEntrants(): VerifyLastMasterServerHistoryEntrantsDTO
    {
        return $this->Entrants;
    }
}