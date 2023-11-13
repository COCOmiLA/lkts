<?php


namespace common\components;


use common\models\EmptyCheck;

class BooleanCaster
{
    




    public static function cast($any_data): bool
    {
        if (is_bool($any_data)) {
            return $any_data;
        }
        if (EmptyCheck::isEmpty($any_data)) {
            return false;
        }
        if (is_string($any_data)) {
            if ($any_data === 'false' || $any_data === '0') {
                return false;
            }
            if ($any_data === 'true' || $any_data === '1') {
                return true;
            }
        }
        return true;
    }

    




    public static function toStr(bool $val): string
    {
        return ($val ? 'true' : 'false');
    }

    




    public static function toInt(bool $val): int
    {
        return ($val ? 1 : 0);
    }
}
