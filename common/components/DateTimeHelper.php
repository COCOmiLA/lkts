<?php

namespace common\components;

use DateTime;
use DateTimeZone;

class DateTimeHelper
{
     




     public static function mstime(): int
     {
          return intval(round(microtime(true) * 1000));
     }

     







     public static function dateFromMstime(string $format, int $mstime = null): string
     {
          if ($mstime === null) {
               $mstime = static::mstime();
          }

          $date = static::createDateFromMstime($mstime);
          return $date->format($format);
     }

     







     private static function createDateFromMstime(int $mstime, \DateTimeZone $time_zone = null)
     {
          if ($time_zone === null) {
               $time_zone = new \DateTimeZone(date_default_timezone_get());
          }

          $microtime = number_format($mstime / 1000, 6, '.', '');

          return DateTime::createFromFormat('U.u', $microtime)->setTimezone($time_zone);
     }

     







     private static function createDateStringFromMstime(string $format, int $mstime = null)
     {
          if ($mstime === null) {
               $mstime = static::mstime();
          }

          $time = intval($mstime / 1000);

          return date($format, $time);
     }
}
