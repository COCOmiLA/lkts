<?php

namespace api\modules\moderator\modules\v1\DTO\FilesTransfer;

use api\modules\moderator\modules\v1\DTO\GetEntrantApplication\PortalEntrantApplicationDTO;
use api\modules\moderator\modules\v1\DTO\ManagerDecideActionDTO\MasterSystemManagerDTO;
use stdClass;

class PostFilesListDTO extends \api\modules\moderator\modules\v1\DTO\BaseXMLSerializedDTO
{
    


    protected $Entrant;

    


    protected $Manager;

    


    protected $Files;

    public function getPropertyEntrant(): PortalEntrantApplicationDTO
    {
        return $this->Entrant;
    }

    public function getPropertyManager(): MasterSystemManagerDTO
    {
        return $this->Manager;
    }

    public function serialize()
    {
        parent::serialize();

        $result = [];
        
        if (isset($this->serializedData->Files) && $raw_files = json_decode(json_encode($this->serializedData->Files))) {
            if (isset($raw_files->FileInfo)) {
                $result = $raw_files->FileInfo;
                if (!is_array($result)) {
                    $result = [$result];
                }
            }
        }
        $this->Files = $result;
    }

    public function getFiles(): array
    {
        return $this->Files;
    }
}