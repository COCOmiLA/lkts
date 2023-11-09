<?php

namespace common\modules\student\models;

use stdClass;
use yii\base\Model;









class ReferenceBase extends Model
{
    private const ATTRIBUTE_LIST = [
        'ReferenceName',
        'ReferenceId',
        'ReferenceUID',
        'ReferenceClassName',
    ];

    public const CLASS_NAME = '';

    public $ReferenceName = null;

    public $ReferenceId = null;

    public $ReferenceUID = null;

    public $ReferenceClassName = null;

    




    function __construct($config = [])
    {
        if (isset($config['rawReference'])) {
            $this->constructByRawData($config['rawReference']);

            unset($config['rawReference']);
        }
        parent::__construct($config);
    }

    




    private function constructByRawData(stdClass $rawReference): void
    {
        foreach (ReferenceBase::ATTRIBUTE_LIST as $attributeName) {
            if (isset($rawReference->{$attributeName})) {
                $this->{$attributeName} = (string) $rawReference->{$attributeName};
            }
        }

        if (static::CLASS_NAME) {
            $this->ReferenceClassName = static::CLASS_NAME;
        }
    }

    


    public function buildFor1C(): stdClass
    {
        $result = [];

        foreach (ReferenceBase::ATTRIBUTE_LIST as $attributeName) {
            $result[$attributeName] = $this->{$attributeName};
        }

        return (object) $result;
    }
}
