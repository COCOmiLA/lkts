<?php

namespace common\modules\abiturient\validators;

use common\modules\abiturient\models\bachelor\EgeResult;
use Yii;
use yii\validators\Validator;
use yii\web\View;

class EgeResultValidator extends Validator
{
    
    private $belowZeroMessage;

    
    private $overOneHundredMessage;

    public function init()
    {
        parent::init();
        $this->belowZeroMessage = Yii::t(
            'abiturient/bachelor/ege/ege-result',
            'Подсказка с ошибкой для поля "discipline_points"; формы ВИ если балл больше максимума: `Балл не может быть меньше 0`'
        );
        $this->overOneHundredMessage = Yii::t(
            'abiturient/bachelor/ege/ege-result',
            'Подсказка с ошибкой для поля "discipline_points"; формы ВИ если балл ниже минимума: `Балл не может быть больше 100`'
        );
    }

    



    public function validateAttribute($model, $attribute)
    {
        if ($model->{$attribute} < 0) {
            $model->addError($attribute, $this->belowZeroMessage);
        } elseif ($model->{$attribute} > 100) {
            $model->addError($attribute, $this->belowZeroMessage);
        }
    }

    






    public function clientValidateAttribute($model, $attribute, $view)
    {
        $formName = strtolower($model->formName());
        $belowZeroMessage = json_encode($this->belowZeroMessage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $overOneHundredMessage = json_encode($this->overOneHundredMessage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return "
            var result = window.checkMinimalScore(\"{$formName}\", \"{$model->id}\", \"{$attribute}\");
            if (result == 1) {
                messages.push('{$overOneHundredMessage}');
            } else if (result == -1) {
                messages.push('{$belowZeroMessage}');
            }
        ";
    }
}
