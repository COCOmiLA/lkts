<?php
namespace api\modules\moderator\modules\v1\DTO\ManagerDecideActionDTO;


use api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO;
use api\modules\moderator\modules\v1\DTO\EntrantPackage\EntrantPackageDTO;

class ManagerDecideActionDTO extends BaseXMLSerializedDTO
{
    


    protected $EntrantPackage;

    


    protected $ManagerComment;

    


    protected $Manager;

    


    public function getPropertyEntrantPackage(): EntrantPackageDTO
    {
        return $this->EntrantPackage;
    }

    


    public function getPropertyManagerComment(): ?string
    {
        return $this->ManagerComment;
    }

    


    public function getPropertyManager(): MasterSystemManagerDTO {
        return $this->Manager;
    }

}