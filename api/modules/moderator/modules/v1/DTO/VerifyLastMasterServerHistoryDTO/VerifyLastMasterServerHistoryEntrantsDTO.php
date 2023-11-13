<?php


namespace api\modules\moderator\modules\v1\DTO\VerifyLastMasterServerHistoryDTO;


use api\modules\moderator\modules\v1\DTO\BaseXMLArraySerializedDTO;

class VerifyLastMasterServerHistoryEntrantsDTO extends BaseXMLArraySerializedDTO
{

    protected $itemType = 'string';

    protected $property = 'EntrantGUID';
}