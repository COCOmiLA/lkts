<?php

namespace common\validators;

use common\models\EmptyCheck;
use yii\validators\NumberValidator;




class CustomNumberValidator extends NumberValidator
{
    private $originalSkipOnEmpty;

    public function validateAttribute($model, $attribute)
    {
        
        if (!$this->integerOnly) {
            parent::validateAttribute($model, $attribute);
        }

        $tmpValue = EmptyCheck::presence($model->{$attribute});
        if ($tmpValue && is_string($tmpValue)) {
            $tmpValue = intval($tmpValue);
        }
        $model->{$attribute} = $tmpValue;

        
        
        if ($this->originalSkipOnEmpty && EmptyCheck::isEmpty($model->{$attribute})) {
            return;
        }

        parent::validateAttribute($model, $attribute);
    }

    public function validateAttributes($model, $attributes = null)
    {
        $this->originalSkipOnEmpty = $this->skipOnEmpty;
        
        $this->skipOnEmpty = false;
        

        parent::validateAttributes($model, $attributes);
        $this->skipOnEmpty = $this->originalSkipOnEmpty;
    }
}
