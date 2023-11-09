<?php


namespace common\components\AddressHelper;


use common\models\dictionary\Fias;

class AddressHelper
{
    public const REGION_TYPE = '1';
    public const AREA_TYPE = '2';
    public const CITY_TYPE = '3';
    public const TOWN_TYPE = '4';
    public const STREET_TYPE = '5';

    public static function getRegion(string $name): AddressQueryBuilder
    {
        return (new AddressQueryBuilder())
            ->setElemName($name)
            ->setWhereCondition(['address_element_type' => self::REGION_TYPE]);
    }

    public static function getArea($region, string $name): AddressQueryBuilder
    {
        $region = self::getValueForQuery($region, 'getRegion', 'region_code');

        return (new AddressQueryBuilder())
            ->setElemName($name)
            ->setWhereCondition(['address_element_type' => self::AREA_TYPE])
            ->setWhereCondition(['region_code' => $region]);
    }

    public static function getCity($region, $area, string $name): AddressQueryBuilder
    {
        $region = self::getValueForQuery($region, 'getRegion', 'region_code');
        $area = self::getValueForQuery($area, 'getArea', 'area_code');

        return (new AddressQueryBuilder())
            ->setElemName($name)
            ->setWhereCondition(['address_element_type' => self::CITY_TYPE])
            ->setWhereCondition(['region_code' => $region])
            ->setWhereCondition(['area_code' => $area]);
    }

    public static function getTown($region, $area, $city, string $name): AddressQueryBuilder
    {
        $region = self::getValueForQuery($region, 'getRegion', 'region_code');
        $area = self::getValueForQuery($area, 'getArea', 'area_code');
        $city = self::getValueForQuery($city, 'getCity', 'city_code');

        return (new AddressQueryBuilder())
            ->setElemName($name)
            ->setWhereCondition(['address_element_type' => self::TOWN_TYPE])
            ->setWhereCondition(['region_code' => $region])
            ->setWhereCondition(['area_code' => $area])
            ->setWhereCondition(['city_code' => $city]);
    }

    public static function getStreet($region, $area, $city, $town, string $name): AddressQueryBuilder
    {
        $region = self::getValueForQuery($region, 'getRegion', 'region_code');
        $area = self::getValueForQuery($area, 'getArea', 'area_code');
        $city = self::getValueForQuery($city, 'getCity', 'city_code');
        $town = self::getValueForQuery($town, 'getTown', 'village_code');

        return (new AddressQueryBuilder())
            ->setElemName($name)
            ->setWhereCondition(['address_element_type' => self::STREET_TYPE])
            ->setWhereCondition(['region_code' => $region])
            ->setWhereCondition(['area_code' => $area])
            ->setWhereCondition(['city_code' => $city])
            ->setWhereCondition(['village_code' => $town]);
    }

    protected static function getValueForQuery($data, $function_name, $column_name)
    {
        if (!($data instanceof FIas) && is_string($data)) {
            $data = self::{$function_name}($data)
                ->getQuery()
                ->select($column_name)
                ->distinct();
        } elseif ($data instanceof FIas) {
            $data = $data->{$column_name};
        } else {
            $data = null;
        }
        return $data;
    }

    public static function list()
    {
        return [
            "АО",
            "Аобл",
            "Респ",
            "Чувашия",
            "а/я",
            "аал",
            "автодорога",
            "аллея",
            "арбан",
            "аул",
            "б-р",
            "берег",
            "вал",
            "взв.",
            "въезд",
            "высел",
            "г",
            "г-к",
            "г.о.",
            "городок",
            "гп",
            "гск",
            "д",
            "днп",
            "дор",
            "дп",
            "ж/д пл-ка",
            "ж/д_будка",
            "ж/д_казарм",
            "ж/д_оп",
            "ж/д_платф",
            "ж/д_пост",
            "ж/д_рзд",
            "ж/д_ст",
            "ж/р",
            "жилрайон",
            "жт",
            "заезд",
            "заимка",
            "зона",
            "казарма",
            "кв-л",
            "км",
            "кольцо",
            "кордон",
            "коса",
            "кп",
            "край",
            "линия",
            "лпх",
            "м",
            "массив",
            "мгстр.",
            "местность",
            "месторожд.",
            "мкр",
            "мост",
            "н/п",
            "наб",
            "нп",
            "обл",
            "ост-в",
            "остров",
            "п",
            "п. ж/д ст.",
            "п/о",
            "п/р",
            "п/ст",
            "парк",
            "пгт",
            "пер",
            "переезд",
            "пл",
            "пл-ка",
            "платф",
            "погост",
            "порт",
            "пос.рзд",
            "починок",
            "пр-кт",
            "проезд",
            "промзона",
            "просек",
            "просека",
            "проселок",
            "проулок",
            "р-н",
            "рзд",
            "рп",
            "ряд",
            "ряды",
            "с",
            "с/а",
            "с/мо",
            "с/о",
            "с/п",
            "с/с",
            "с/т",
            "сад",
            "сзд.",
            "сквер",
            "сл",
            "снт",
            "сп.",
            "спуск",
            "ст",
            "ст-ца",
            "стр",
            "тер",
            "тер. ГСК",
            "тер. ДНО",
            "тер. ДНП",
            "тер. ДНТ",
            "тер. ДПК",
            "тер. ОНО",
            "тер. ОНП",
            "тер. ОНТ",
            "тер. ОПК",
            "тер. ПК",
            "тер. СНО",
            "тер. СНП",
            "тер. СНТ",
            "тер. СПК",
            "тер. ТСН",
            "тер.СОСН",
            "тер.ф.х.",
            "тракт",
            "туп",
            "ул",
            "у",
            "ус.",
            "уч-к",
            "ф/х",
            "ферма",
            "х",
            "ш",
        ];
    }

    public static function federalSignificanceCityCodes()
    {
        return [
            '7700000000000', 
            '7800000000000', 
            '9200000000000', 
            '9900000000000', 
        ];
    }
}