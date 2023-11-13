<?php


namespace common\components\ArrayToXmlConverter;


use common\models\ToAssocCaster;
use DOMDocument;
use SimpleXMLElement;
use Throwable;
use yii\helpers\ArrayHelper;

class ArrayToXmlConverter
{
    public static function to_xml($any_data, string $root_name = 'Root', array $root_attrs = []): string
    {
        $error_occurred = false;
        $assoc_data = null;
        try {
            $assoc_data = self::ensure_assoc($any_data);
        } catch (\Throwable $e) {
            $error_occurred = true;
        }
        if ($error_occurred || !is_array($assoc_data)) {
            return print_r($assoc_data, true);
        }
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$root_name}/>");
        foreach ($root_attrs as $attr_name => $attr_value) {
            $xml->addAttribute($attr_name, $attr_value);
        }
        self::array_to_xml($assoc_data, $xml);
        return self::getPrettyXML($xml->asXML());
    }

    








    public static function removeNameSpaces(string $xml): string
    {
        return preg_replace('/(<\/*)[^ >:]*:/', '$1', $xml);
    }

    private static function getPrettyXML(string $xml)
    {
        try {
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml);
            return html_entity_decode($dom->saveXML($dom, LIBXML_NOEMPTYTAG), ENT_NOQUOTES, 'UTF-8');
        } catch (Throwable $e) {
            return $xml;
        }
    }

    private static function array_to_xml(array $array, SimpleXMLElement &$xml, string $root_key = null): void
    {
        foreach ($array as $key => $value) {
            self::convert_elem_to_xml($root_key && is_numeric($key) ? $root_key : $key, $value, $xml);
        }
    }

    





    private static function convert_elem_to_xml(string $key, $value, SimpleXMLElement &$xml): void
    {
        $key = (is_numeric($key) ? $xml->getName() : $key);
        if (is_array($value)) {
            if (ArrayHelper::isAssociative($value)) {
                $sub_node = $xml->addChild($key);
                self::array_to_xml($value, $sub_node);
            } else {
                self::array_to_xml($value, $xml, $key);
            }
        } else {
            $xml->addChild($key, htmlentities((string)$value, ENT_XML1));
        }
    }

    public static function ensure_assoc($any_data)
    {
        return ToAssocCaster::getAssoc($any_data);
    }

    public static function remove_xml_tag(string $xml_string): string
    {
        return trim(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml_string));
    }
}