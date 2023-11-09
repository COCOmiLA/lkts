<?php
namespace common\components\ReferenceTypeManager\exceptions;




class ReferenceManagerCannotSerializeDataException extends \Exception
{
    private $referenceData = null;

    public function __construct($referenceData = null, $code = 0, \Throwable $previous = null)
    {
        $this->referenceData = $referenceData;
        parent::__construct("Невозможно сериализовать класс ReferenceType.", $code, $previous);
    }

    


    public function getReferenceData()
    {
        return $this->referenceData;
    }
}