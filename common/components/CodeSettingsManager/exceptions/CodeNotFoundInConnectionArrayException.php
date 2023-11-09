<?php


namespace common\components\CodeSettingsManager\exceptions;


use Throwable;






class CodeNotFoundInConnectionArrayException extends \Exception
{
    








    public function __construct($defaultCode, $arrName, $code = 0, Throwable $previous = null)
    {
        parent::__construct("Не удалось найти элемент ' '{$defaultCode}' в массиве {$arrName}", $code, $previous);
    }
}