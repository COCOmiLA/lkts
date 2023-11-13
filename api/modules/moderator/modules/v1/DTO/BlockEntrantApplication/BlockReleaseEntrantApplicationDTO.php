<?php
namespace api\modules\moderator\modules\v1\DTO\BlockEntrantApplication;

use api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO;
use api\modules\moderator\modules\v1\DTO\GetEntrantApplication\PortalEntrantApplicationDTO;
use api\modules\moderator\modules\v1\DTO\ManagerDecideActionDTO\MasterSystemManagerDTO;

class BlockReleaseEntrantApplicationDTO extends BaseXMLSerializedDTO
{
    


    protected $PortalEntrantApplication;

    


    protected $Manager;

    public function getPropertyPortalEntrantApplication(): PortalEntrantApplicationDTO {
        return $this->PortalEntrantApplication;
    }

    


    public function getPropertyManager(): MasterSystemManagerDTO {
        return $this->Manager;
    }

}