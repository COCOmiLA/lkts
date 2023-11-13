<?php


namespace common\models\interfaces;





interface IFillableReferenceDictionary
{
    public static function getReferenceClassToFill(): string;

    




    public function fillDictionary();

}