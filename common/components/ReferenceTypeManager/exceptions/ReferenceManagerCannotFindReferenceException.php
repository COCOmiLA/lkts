<?php
namespace common\components\ReferenceTypeManager\exceptions;




class ReferenceManagerCannotFindReferenceException extends \Exception
{
    private $referenceData = null;

    public function __construct($referenceData = null, $code = 0, \Throwable $previous = null)
    {
        $this->referenceData = $referenceData;
        parent::__construct("Невозможно найти Reference Type в системе портала.\nПОЛУЧЕННОЕ ЗНАЧЕНИЕ:\n\n" . print_r($referenceData, true), $code, $previous);
    }

    


    public function getReferenceData()
    {
        return $this->referenceData;
    }
}