<?php

namespace api\modules\moderator\modules\v1\DTO\BlockEntrantApplication;

use api\modules\moderator\modules\v1\DTO\GetEntrantApplication\UniversalResponseDTO;
use common\components\BooleanCaster;

class PortalEntrantApplicationWithModeratorAndStateDTO extends PortalEntrantApplicationWithModeratorDTO
{
    


    protected $State;

    public function getPropertyState(): UniversalResponseDTO
    {
        return $this->State;
    }

    public function getIsSuccess(): bool
    {
        return $this->State && BooleanCaster::cast($this->State->getPropertyComplete());
    }
    public function getComment(): string
    {
        return $this->State ? $this->State->getPropertyDescription() : '';
    }
}