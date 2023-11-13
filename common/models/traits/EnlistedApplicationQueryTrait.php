<?php

namespace common\models\traits;

trait EnlistedApplicationQueryTrait
{
    


    public static function getJoinWith(): string
    {
        return 'bachelorSpecialities';
    }

    


    public static function getInQueryValue()
    {
        return [false, null];
    }
}
