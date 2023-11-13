<?php

namespace api\modules\moderator\modules\v1\DTO\GetEntrantApplication;

use api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO;







class UniversalResponseDTO extends BaseXMLSerializedDTO
{
    


    protected $Complete;

    


    protected $Description;

    


    public function getPropertyComplete(): bool
    {
        return $this->Complete;
    }

    public function getPropertyDescription(): string
    {
        return $this->Description;
    }

}