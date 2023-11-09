<?php

namespace common\services\abiturientController\questionary;



class InitializationQuestionaryService extends AbiturientQuestionaryService
{
    


    public function checkTimeZone(): bool
    {
        $timeZoneLocal = date_default_timezone_get();
        $timeZoneGlobal = ini_get('date.timezone');

        if (strcmp($timeZoneLocal, $timeZoneGlobal) || strlen((string)$timeZoneGlobal) < 1) {
            return true;
        }

        return false;
    }
}
