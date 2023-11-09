<?php

namespace common\modules\abiturient\validators\PassportData;

use common\components\CodeSettingsManager\CodeSettingsManager;
use common\modules\abiturient\assets\validationAsset\PassportDataValidationAsset;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\models\PassportData;
use Yii;
use yii\validators\Validator;
use yii\web\View;

class PassportDataValidation extends Validator
{
    
    private $seriesMessage;

    
    private $numberMessage;

    public function init()
    {
        parent::init();
        $this->seriesMessage = Yii::t(
            'abiturient/questionary/passport-data',
            'Подсказка с ошибкой для поля "series" формы "Паспортные данные": `Серия паспорта РФ должна содержать 4 символа`'
        );
        $this->numberMessage = Yii::t(
            'abiturient/questionary/passport-data',
            'Подсказка с ошибкой для поля "number" формы "Паспортные данные": `Номер паспорта РФ должен содержать 6 символов`'
        );
    }

    





    public function validateAttribute($model, $attribute)
    {
        if (!$this->{"{$attribute}IsCorrect"}($model)) {
            $model->addError($attribute, $this->{"{$attribute}Message"});
        }
    }

    






    public function clientValidateAttribute($model, $attribute, $view)
    {
        $documentTypePassportGuid = CodeSettingsManager::GetEntityByCode('russian_passport_guid');
        $view->registerJsVar('documentTypePassportGuid', $documentTypePassportGuid->id);
        PassportDataValidationAsset::register($view);

        $modelId = $model->id ?? 0;
        $formName = strtolower($model->formName());
        $message = json_encode($this->{"{$attribute}Message"}, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return "
            var isSuccessValidate = window.{$attribute}IsCorrect(\"{$formName}\", \"{$modelId}\", \"{$attribute}\");
            if (!isSuccessValidate) {
                messages.push('{$message}');
            }
        ";
    }

    




    public function seriesIsCorrect($model): bool
    {
        return $this->attributeIsCorrect($model, 'series', 4);
    }

    




    public function numberIsCorrect($model): bool
    {
        return $this->attributeIsCorrect($model, 'number', 6);
    }

    






    public function attributeIsCorrect($model, string $attribute, int $charsLength): bool
    {
        $documentType = CodeSettingsManager::GetEntityByCode('russian_passport_guid');
        if ((int) $model->document_type_id === $documentType->id) {
            if (strlen((string)$model->{$attribute}) !== $charsLength) {
                return false;
            }
        }

        return true;
    }
}
