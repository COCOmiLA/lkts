<?php

namespace common\modules\abiturient\validators;

use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\views\bachelor\assets\CompetitiveGroupEntranceTestsValidatorAsset;
use Yii;
use yii\validators\Validator;
use yii\web\View;

class CompetitiveGroupEntranceTestsValidator extends Validator
{
    
    private $errorMessage;

    public function init()
    {
        parent::init();
        $this->errorMessage = Yii::t(
            'abiturient/bachelor/ege/ege-result',
            'Подсказка с ошибкой для поля "cget_discipline_id"; формы ВИ: `Выберите вступительное испытание.`'
        );
    }

    



    public function validateAttribute($model, $attribute)
    {
        if (empty($model->cget_discipline_id) || $model->cget_discipline_id == 0) {
            $model->addError($attribute, $this->errorMessage);
        }
    }

    






    public function clientValidateAttribute($model, $attribute, $view)
    {
        CompetitiveGroupEntranceTestsValidatorAsset::register($view);

        $errorMessage = json_encode($this->errorMessage, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return "
            var result = window.disciplineValidator(\"{$model->index}\");
            if (result) {
                messages.push('{$errorMessage}');
            }
        ";
    }
}
