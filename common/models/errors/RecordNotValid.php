<?php

namespace common\models\errors;

use yii\base\Model;

class RecordNotValid extends \Exception
{
    


    public function __construct(Model $model)
    {
        $form_name = $model->formName();
        $attributes = print_r($model->getAttributes(), true);
        $errors = print_r($model->errors, true);
        parent::__construct("Ошибка при сохранении данных {$form_name}: {$errors}\n{$attributes}");
    }
}
