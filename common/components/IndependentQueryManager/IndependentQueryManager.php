<?php

namespace common\components\IndependentQueryManager;

use common\components\LikeQueryManager;
use Yii;












class IndependentQueryManager
{
    private const MYSQL_DATE_FORMAT = '%d.%m.%Y';
    private const MYSQL_DATETIME_FORMAT = '%Y-%m-%d %H:%i:%s';

    private const POSTGRES_DATE_FORMAT = 'DD.MM.YYYY';
    private const POSTGRES_DATETIME_FORMAT = 'YYYY-MM-DD HH24:MI:SS';

    




    private static function getDbType(): string
    {
        return Yii::$app->db->driverName;
    }

    




    private static function isMysql(): bool
    {
        return IndependentQueryManager::getDbType() === 'mysql';
    }

    




    private static function isPgsql(): bool
    {
        return IndependentQueryManager::getDbType() === 'pgsql';
    }

    








    public static function toBinary(string $query, string $alias = ''): string
    {
        $result = $query;
        if (IndependentQueryManager::isMysql()) {
            $result = "BINARY {$query}";
        }
        if (IndependentQueryManager::isPgsql()) {
            $result = "CONVERT_TO({$query}, 'UTF8')";
        }

        $result .= IndependentQueryManager::addAlias($alias);

        return $result;
    }

    









    private static function toDateTimeFromTimestamp(string $query, string $dateTimeFormat, string $alias = ''): string
    {
        $result = $query;
        if (IndependentQueryManager::isMysql()) {
            $result = "DATE_FORMAT(FROM_UNIXTIME({$query}), '{$dateTimeFormat}')";
        } elseif (IndependentQueryManager::isPgsql()) {
            $result = "TO_CHAR(DATE(TO_TIMESTAMP({$query})), '{$dateTimeFormat}')";
        }

        $result .= IndependentQueryManager::addAlias($alias);

        return $result;
    }

    








    public static function toDate(string $query, string $alias = ''): string
    {
        $dateTimeFormat = IndependentQueryManager::getDateFormat();
        return IndependentQueryManager::toDateTimeFromTimestamp($query, $dateTimeFormat, $alias);
    }

    








    public static function toDateTime(string $query, string $alias = ''): string
    {
        $dateTimeFormat = IndependentQueryManager::getDateTimeFormat();
        return IndependentQueryManager::toDateTimeFromTimestamp($query, $dateTimeFormat, $alias);
    }

    









    public static function toDateTimeFromString(string $query, string $dateTimeFormat, string $alias = ''): string
    {
        $result = $query;
        if (IndependentQueryManager::isMysql()) {
            $result = "STR_TO_DATE({$query}, '{$dateTimeFormat}')";
        } elseif (IndependentQueryManager::isPgsql()) {
            $transformFunction = 'TO_TIMESTAMP';
            if ($dateTimeFormat == IndependentQueryManager::POSTGRES_DATE_FORMAT) {
                $transformFunction = 'TO_DATE';
            }

            $result = "{$transformFunction}({$query}, '{$dateTimeFormat}')";
        }

        $result .= IndependentQueryManager::addAlias($alias);

        return $result;
    }

    








    public static function strToDate(string $query, string $alias = ''): string
    {
        $dateTimeFormat = IndependentQueryManager::getDateFormat();
        return IndependentQueryManager::toDateTimeFromString($query, $dateTimeFormat, $alias);
    }

    public static function strToDateTime(string $query, string $alias = ''): string
    {
        $dateTimeFormat = IndependentQueryManager::getDateTimeFormat();
        return IndependentQueryManager::toDateTimeFromString($query, $dateTimeFormat, $alias);
    }

    






    private static function addAlias(string $alias): string
    {
        $result = '';

        if ($alias) {
            if (IndependentQueryManager::isPgsql()) {
                $alias = "\"{$alias}\"";
            }
            $result .= " AS {$alias}";
        }

        return $result;
    }


    




    public static function getDateTimeFormat(): string
    {
        $dateTimeFormat = '';
        if (IndependentQueryManager::isMysql()) {
            $dateTimeFormat = IndependentQueryManager::MYSQL_DATETIME_FORMAT;
        } elseif (IndependentQueryManager::isPgsql()) {
            $dateTimeFormat = IndependentQueryManager::POSTGRES_DATETIME_FORMAT;
        }
        return $dateTimeFormat;
    }

    




    private static function getDateFormat(): string
    {
        $dateTimeFormat = '';
        if (IndependentQueryManager::isMysql()) {
            $dateTimeFormat = IndependentQueryManager::MYSQL_DATE_FORMAT;
        } elseif (IndependentQueryManager::isPgsql()) {
            $dateTimeFormat = IndependentQueryManager::POSTGRES_DATE_FORMAT;
        }
        return $dateTimeFormat;
    }

    public static function quoteEntity(string $entity): string
    {
        if (IndependentQueryManager::isPgsql()) {
            return "\"{$entity}\"";
        }
        return "`{$entity}`";
    }

    public static function quoteString(string $value): string
    {
        return "'{$value}'";
    }

    







    public static function searchOnlyByDigitsCondition(string $column, string $compare_to): array
    {
        $digits = preg_replace('/[^0-9.]+/', '', $compare_to);
        if (static::isPgsql()) {
            return [LikeQueryManager::getActionName(), "REGEXP_REPLACE({$column}, '\D', '', 'g')", $digits];
        } else {
            $regexp = '^[^0-9]*' . implode('[^0-9]*', str_split($digits, 1));
            return ['REGEXP', $column, $regexp];
        }
    }
}
