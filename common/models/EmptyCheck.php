<?php

namespace common\models;

use yii\base\BaseObject;






class EmptyCheck
{
    










    public static function isEmpty($var): bool
    {
        return (
            is_null($var) ||
            (
                is_string($var) &&
                trim((string)$var) === ''
            ) ||
            (
                is_object($var) &&
                (
                    empty(get_object_vars($var)) &&

                    
                    
                    !($var instanceof BaseObject)
                )
            ) ||
            (
                is_array($var) &&
                empty($var)
            )
        );
    }

    






    public static function presence($val)
    {
        if (EmptyCheck::isEmpty($val)) {
            return null;
        }
        return $val;
    }

    







    public static function isLoadingStringOrEmpty($string): bool
    {
        if (EmptyCheck::isEmpty($string)) {
            return true;
        }
        return (bool) preg_match('/[A-Za-zА-Яа-я]+\ +\.+/', $string);
    }

    




    public static function isNonEmptyJson($string): bool
    {
        if (EmptyCheck::isEmpty($string)) {
            return false;
        }
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
