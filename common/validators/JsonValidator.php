<?php

namespace common\validators;

use Yii;
use yii\validators\Validator;




class JsonValidator extends Validator
{
    


    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('common', '"{attribute}" должен содержать валидный JSON');
        }
    }

    


    public function validateValue($value)
    {
        if (!json_decode((string)$value)) {
            return [$this->message, []];
        }
        return null;
    }

    


    public function clientValidateAttribute($model, $attribute, $view)
    {
        $message = Yii::$app->getI18n()->format($this->message, [
            'attribute' => $model->getAttributeLabel($attribute)
        ], Yii::$app->language);
        return "try {
                JSON.parse(value);
            } catch (e) {
                messages.push('{$message}');
            }";
    }
}
