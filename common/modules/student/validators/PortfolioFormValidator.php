<?php

namespace common\modules\student\validators;

use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\student\validators\assets\PortfolioFormValidatorAsset;
use yii\validators\Validator;
use yii\web\View;

class PortfolioFormValidator extends Validator
{
    private const MESSAGE_TEMPLATE = 'Максимальная длина не может быть больше чем {MAX_LENGTH}';

    
    public $maxLength;

    public function init()
    {
        parent::init();
    }

    



    public function validateAttribute($model, $attribute)
    {
        if ($model->{$attribute} > $this->maxLength) {
            $vars = ['{MAX_LENGTH}' => $this->maxLength];
            $message = strtr(self::MESSAGE_TEMPLATE, $vars);

            $model->addError($attribute, $message);
        }
    }

    






    public function clientValidateAttribute($model, $attribute, $view)
    {
        PortfolioFormValidatorAsset::register($view);

        $attribute = mb_strtolower(strtolower($attribute));
        $formName = mb_strtolower(strtolower($model->formName()));
        $maxLength = $this->maxLength == INF ? 'Infinity' : $this->maxLength;

        $vars = ['{MAX_LENGTH}' => $this->maxLength];
        $message = strtr(self::MESSAGE_TEMPLATE, $vars);
        $maxLengthMessage = json_encode($message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return "
            var result = isLengthMoreThan(\"{$formName}\", \"{$attribute}\", {$maxLength});
            if (result) {
                messages.push('{$maxLengthMessage}');
            }
        ";
    }
}
