<?php

namespace common\components\keyStorage;

use Yii;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;


















class FormModel extends Model
{
    const TYPE_DROPDOWN = 'dropdownList';
    const TYPE_TEXTINPUT = 'textInput';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIOLIST = 'radioList';
    const TYPE_CHECKBOXLIST = 'checkboxList';
    const TYPE_WIDGET = 'widget';

    


    protected $keys = [];
    


    protected $map = [];

    


    public $keyStorage = 'keyStorage';

    


    protected $attributes;

    


    public function setKeys($keys)
    {
        $variablized = $values = [];
        foreach ($keys as $key => $data) {
            $variablizedKey = Inflector::variablize($key);
            $this->map[$variablizedKey] = $key;
            $values[$variablizedKey] = $this->getKeyStorage()->get($key, null, false);
            $variablized[$variablizedKey] = $data;
        }
        $this->keys = $variablized;
        foreach ($values as $k => $v) {
            $this->setAttribute($k, $v);
        }
        parent::init();
    }

    


    public function getKeys()
    {
        return $this->keys;
    }

    





    public function attributes()
    {
        $names = [];
        foreach ($this->keys as $attribute => $values) {
            $names[] = $attribute;
        }

        return $names;
    }

    


    public function rules()
    {
        $rules = [];
        foreach ($this->keys as $attribute => $data) {
            $attributeRules =  ArrayHelper::getValue($data, 'rules', []);
            if (!empty($attributeRules)) {
                foreach ($attributeRules as $rule) {
                    array_unshift($rule, $attribute);
                    $rules[] = $rule;
                }
            } else {
                $rules[] = [$attribute, 'safe'];
            }

        }
        return $rules;
    }

    


    public function attributeLabels()
    {
        $labels = [];
        foreach ($this->keys as $attribute => $data) {
            $label = is_array($data) ? ArrayHelper::getValue($data, 'label') : $data;
            $labels[$attribute] = $label;
        }
        return $labels;
    }

    




    public function save($runValidation = true)
    {
        if ($runValidation && !$this->validate()) {
            return false;
        }
        foreach ($this->attributes as $variablizedKey => $value) {
            $originalKey = ArrayHelper::getValue($this->map, $variablizedKey);
            if (!$originalKey) {
                throw new Exception();
            }
            $this->getKeyStorage()->set($originalKey, $value);
        }
        return true;
    }

    



    protected function getKeyStorage()
    {
        return Yii::$app->get($this->keyStorage);
    }

    








    public function __get($name)
    {
        if (isset($this->attributes[$name]) || array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        } elseif ($this->hasAttribute($name)) {
            return null;
        } else {
            $value = parent::__get($name);
            return $value;
        }
    }

    





    public function __set($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->attributes[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    





    public function __isset($name)
    {
        try {
            return $this->__get($name) !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    





    public function __unset($name)
    {
        if ($this->hasAttribute($name)) {
            unset($this->attributes[$name]);
        }
    }

    




    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]) || in_array($name, $this->attributes(), false);
    }

    







    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    






    public function setAttribute($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->attributes[$name] = $value;
        } else {
            throw new InvalidArgumentException(get_class($this) . ' has no attribute named "' . $name . '".');
        }
    }
}
