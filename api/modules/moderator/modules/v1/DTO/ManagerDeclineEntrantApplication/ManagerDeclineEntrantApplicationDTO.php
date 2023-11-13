<?php
namespace api\modules\moderator\modules\v1\DTO\ManagerDeclineEntrantApplication;

use api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO;
use api\modules\moderator\modules\v1\DTO\GetEntrantApplication\PortalEntrantApplicationDTO;
use api\modules\moderator\modules\v1\DTO\ManagerDecideActionDTO\MasterSystemManagerDTO;

class ManagerDeclineEntrantApplicationDTO extends BaseXMLSerializedDTO
{
    


    protected $PortalEntrantApplication;
    


    protected $ManagerComment;

    


    protected $Manager;

    public function getPropertyPortalEntrantApplication(): PortalEntrantApplicationDTO {
        return $this->PortalEntrantApplication;
    }

    


    public function getPropertyManager(): MasterSystemManagerDTO {
        return $this->Manager;
    }

    


    public function getPropertyManagerComment(): ?string
    {
        return $this->ManagerComment;
    }
}