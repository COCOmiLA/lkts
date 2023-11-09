<?php


namespace common\components\ReferenceTypeManager\exceptions;


use Throwable;

class ReferenceManagerBadGetReferenceRequest extends \Exception
{
    


    private $parameter = "";
    


    private $parameterRef = "";
    


    private $parameterType = "";

    public function __construct(string $parameter, string $parameterRef, string $parameterType, $code = 0, Throwable $previous = null)
    {
        $this->parameter = $parameter;
        $this->parameterType = $parameterType;
        $this->parameterRef = $parameterRef;
        parent::__construct("Ошибка при обращении к методу GetReference. ()", $code, $previous);
    }

}