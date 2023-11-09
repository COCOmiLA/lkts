<?php

namespace api\modules\moderator\modules\v1\DTO\FilesTransfer;

use api\modules\moderator\modules\v1\DTO\GetEntrantApplication\PortalEntrantApplicationDTO;
use api\modules\moderator\modules\v1\DTO\ManagerDecideActionDTO\MasterSystemManagerDTO;

class GetFilesListDTO extends \api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO
{
    


    protected $Entrant;

    


    protected $Manager;


    public function getPropertyEntrant(): PortalEntrantApplicationDTO {
        return $this->Entrant;
    }

    


    public function getPropertyManager(): MasterSystemManagerDTO {
        return $this->Manager;
    }
}