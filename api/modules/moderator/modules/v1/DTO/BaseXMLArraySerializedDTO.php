<?php

namespace api\modules\moderator\modules\v1\DTO;


use api\modules\moderator\modules\v1\DTO\exceptions\DTOCannotSerializeDataException;
use api\modules\moderator\modules\v1\DTO\exceptions\DTOFieldNotFoundException;
use api\modules\moderator\modules\v1\DTO\interfaces\IXmlDTO;
use common\components\ArrayToXmlConverter\ArrayToXmlConverter;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use SimpleXMLElement;

class BaseXMLArraySerializedDTO implements IXmlDTO
{
    



    protected $itemType;

    



    protected $property = 'item';

    



    private $items = [];

    



    private $rawData = '';

    



    private $serializedData = null;

    




    public function __construct(string $rawData = null)
    {
        if (!is_null($rawData)) {
            $this->rawData = ArrayToXmlConverter::removeNameSpaces($rawData);
            $this->serializedData = new SimpleXMLElement($this->rawData);
            $this->serialize();
        }
    }

    




    public function getProperties(): array
    {
        return array_filter((new ReflectionClass($this))->getMethods(ReflectionProperty::IS_PUBLIC), function (ReflectionMethod $method) {
            return !empty($this->getPropertyByMethod($method->getName()));
        });
    }

    



    public function serialize()
    {
        $type = $this->itemType;

        if (empty($type)) {
            throw new DTOCannotSerializeDataException('Не указан тип для сериализации массива.');
        }

        $rawArray = $this->serializedData->{$this->property};
        foreach ($rawArray as $item) {
            $this->pushElementToArray($item);
        }
    }

    



    protected function isTypeBuildIn()
    {
        return !class_exists($this->itemType);
    }


    



    protected function getPropertyByMethod($method)
    {
        if (preg_match('/getProperty(.*)/', $method, $output_array)) {
            return $output_array[1];
        } else {
            return null;
        }
    }

    




    public function setSerializedData(SimpleXMLElement $serializedData): void
    {
        $this->serializedData = $serializedData;
        $this->checkFieldProvided($this->property);
        $this->serialize();
    }

    



    public function setStringRawData(string $rawData): void
    {
        $this->rawData = ArrayToXmlConverter::removeNameSpaces($rawData);
        $this->setSerializedData(new SimpleXMLElement($this->rawData));
    }

    public function __get($name)
    {
        if (method_exists($this, "getProperty{$name}")) {
            return call_user_func(array($this, "getProperty{$name}"));
        }
        return $this->{$name};
    }

    


    public function getItems(): array
    {
        return $this->items;
    }

    private function pushElementToArray($el)
    {
        $data = null;
        if ($this->isTypeBuildIn()) {
            settype($el, $this->itemType);
            $data = $el;
        } else {
            $typeClassString = (string)$this->itemType;
            $typeClass = new $typeClassString();
            $typeClass->setSerializedData($el);
            $data = $typeClass;
        }
        $this->items[] = $data;
    }


    public function checkFieldProvided($field): bool
    {
        if (!isset($this->serializedData->{$field})) {
            throw new DTOFieldNotFoundException($field);
        }
        return true;
    }
}