<?php


namespace common\models;


use common\models\interfaces\IHaveReferenceClassName;

class ReferenceTypeModelFrom1C extends CodeModelFrom1C implements IHaveReferenceClassName
{
    protected static $referenceClassName = '';

    


    public static function getReferenceClassName(): string
    {
        return static::$referenceClassName;
    }
}