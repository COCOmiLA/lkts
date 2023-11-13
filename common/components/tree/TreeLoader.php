<?php

namespace common\components\tree;

use Yii;
use yii\base\Component;

class TreeLoader extends Component
{
    public function loadTree($xmlArray){
        return $this->BuildTreeFromXML($xmlArray);
    }

    protected function BuildPropertiesFromXML($data){
        $xml_properties = $data;
        $properties = [];
        if (is_array($xml_properties)) {
            foreach ($xml_properties as $xml_property) {
                $property = new models\Property();
                $property->name = $xml_property->Property->ReferenceName;
                $property->value = $this->BuildPropertyValueFromXML($xml_property);

                $properties[] = $property;
            }
        } else {
            $xml_property = $xml_properties;
            $property = new models\Property();
            $property->name = $xml_property->Property->ReferenceName;
            $property->value = $this->BuildPropertyValueFromXML($xml_property);

            $properties[] = $property;
        }

        return $properties;
    }

    protected function BuildPropertyValueFromXML($data) {
        return $data->Value ?? $data->ValueRef->ReferenceName;
    }

    protected function BuildLapResultFromXML($data){
        $xml_types = $data;
        
        if (is_array($xml_types)) {
            foreach ($xml_types as $xml_type) {
                $type = new models\TypeActivity();
                $type->properties = $this->BuildPropertiesFromXML($xml_type->LapResultProperties);
            }
        } else {
            $xml_type = $xml_types;
            $type = new models\TypeActivity();
            $type->properties = $this->BuildPropertiesFromXML($xml_type->LapResultProperties);
        }

        return $type;
    }

    protected function BuildTreeFromXML($data)
    {
        $tree = [];

        if (isset($data->LapStrings)) {
            if (is_array($data->LapStrings)) {
                $tempArray = [];

                foreach ($data->LapStrings as $element) {
                    if(isset($element->LapStrings)) {
                        $tempArray[$element->LapName] = $this->BuildTreeFromXML($element->LapStrings);
                    } else {
                        $tempArray[$element->LapName] = '';
                    }
                }

                $tree[$data->LapName] = $tempArray;
            } else {
                $tree[$data->LapName] = $this->BuildTreeFromXML($data->LapStrings);
            }
        }

        if (isset($data->LapResultStrings)) {

            $tree[$data->LapName] = ['name' => $data->LapResultStrings->ResultForm->ReferenceName,
                'properties' => $this->BuildLapResultFromXML($data->LapResultStrings)];
        }


        if (isset($data->LapName)) {
            $tree[$data->LapName] = ['name' => $data->LapName,
                'properties' =>  ($data)] ;
        }


        return $tree;
    }
}