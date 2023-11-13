<?php


namespace common\components\ReferenceTypeManager\exceptions;


use Throwable;

class ReferenceManagerWrongReferenceTypeClassException extends \Exception
{
    


    private $referenceClass = "";

    public function __construct($referenceClass, $code = 0, Throwable $previous = null)
    {
        $this->referenceClass = $referenceClass;
        parent::__construct("Ожидался класс наследующий или декорирующий класс StoredReferenceType или OData.", $code, $previous);
    }

}