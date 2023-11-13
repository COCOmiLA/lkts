<?php


namespace common\models;


class ToAssocCaster
{
    public static function getAssoc($raw)
    {
        $raw = json_decode(json_encode($raw), true);
        if (EmptyCheck::isEmpty($raw)) {
            return [];
        }
        return static::processEmptyArrays($raw);
    }

    private static function processEmptyArrays($assoc)
    {
        if (is_array($assoc) && isset($assoc['enc_value'])) {
            $assoc = $assoc['enc_value'];
        }
        if (is_array($assoc)) {
            foreach ($assoc as $_ => &$value) {
                if (is_array($value) && EmptyCheck::isEmpty($value)) {
                    $value = '';
                } else {
                    $value = static::processEmptyArrays($value);
                }
            }
        }
        return $assoc;
    }
}