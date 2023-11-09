<?php

namespace common\helpers;




class Functions
{
    public static function array_flatten(array $array): array
    {
        $return = [];
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[] = $a;
        });
        return $return;
    }

    public static function zip(...$arrays)
    {
        $lengths = array_map('count', $arrays);
        $minLength = min($lengths);
        $result = [];

        for ($i = 0; $i < $minLength; $i++) {
            $result[] = array_map(function ($array) use ($i) {
                return $array[$i];
            }, $arrays);
        }

        return $result;
    }

    public static function zip_longest(...$arrays)
    {
        $maxLength = max(array_map('count', $arrays));
        $result = [];

        for ($i = 0; $i < $maxLength; $i++) {
            $result[] = array_map(function ($array) use ($i) {
                return $array[$i] ?? null;
            }, $arrays);
        }

        return $result;
    }

}