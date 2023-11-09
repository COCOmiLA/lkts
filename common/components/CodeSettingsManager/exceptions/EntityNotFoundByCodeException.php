<?php


namespace common\components\CodeSettingsManager\exceptions;


use Throwable;






class EntityNotFoundByCodeException extends \Exception
{
    









    public function __construct($defaultCodeName, $tableName, $fieldName, $codeValue, $code = 0, Throwable $previous = null)
    {
        parent::__construct("При попытке найти элемент справочника по коду по умолчанию \"$defaultCodeName\" возникла ошибка. Не удалось найти элемент по полю \"{$fieldName}\" с значением \"{$codeValue}\" в таблице \"{$tableName}\". 
        Пожалуйста обратитесь к администратору. Информация для администратора: в панели администратора необходимо открыть страницу \"Настройки личного кабинета поступающего\"-> \"Коды по умолчанию\" и проверить правильность заполнение кодов по умолчанию.", $code, $previous);
    }
}