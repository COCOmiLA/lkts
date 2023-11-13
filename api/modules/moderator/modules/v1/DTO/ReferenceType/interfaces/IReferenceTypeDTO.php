<?php
namespace api\modules\moderator\modules\v1\DTO\ReferenceType\interfaces;


use api\modules\moderator\modules\v1\DTO\interfaces\IXmlDTO;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;

interface IReferenceTypeDTO extends IXmlDTO
{
    



    public function getReferenceTypeId(): ?int;

    public function getStoredReferenceType(): ?StoredReferenceType;

    public function getStoredReferenceTypeClass(): string;

    public function setArrayRawData(array $rawData);
}