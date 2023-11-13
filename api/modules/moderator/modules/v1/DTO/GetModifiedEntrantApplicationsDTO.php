<?php
namespace api\modules\moderator\modules\v1\DTO;

use api\modules\moderator\modules\v1\DTO\ReferenceType\CampaignRefDTO;






class GetModifiedEntrantApplicationsDTO extends BaseXMLSerializedDTO
{
    


    protected $CampaignRef;


    


    public function getPropertyCampaignRef(): CampaignRefDTO
    {
        return $this->CampaignRef;
    }

}