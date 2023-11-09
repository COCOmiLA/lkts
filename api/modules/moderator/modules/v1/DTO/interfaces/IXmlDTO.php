<?php


namespace api\modules\moderator\modules\v1\DTO\interfaces;


use SimpleXMLElement;

interface IXmlDTO
{
    


    public function serialize();

    public function setStringRawData(string $rawData);

    public function setSerializedData(SimpleXMLElement $serializedData);

    




    public function checkFieldProvided($field): bool;
}