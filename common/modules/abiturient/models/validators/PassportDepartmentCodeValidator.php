<?php

namespace common\modules\abiturient\models\validators;

use common\models\dictionary\DocumentType;
use common\models\EmptyCheck;
use Yii;
use yii\validators\Validator;

class PassportDepartmentCodeValidator extends Validator
{

    public function validateAttribute($model, $attribute)
    {
        $department_code = $model->$attribute;
        if ($model->documentType) {
            if (Yii::$app->configurationManager->getCode('russian_passport_guid') === $model->documentType->ref_key) {
                if (!EmptyCheck::isEmpty($department_code) && preg_match('/([0-9]{3}-[0-9]{3})/', $department_code) === 0) {
                    $this->addError(
                        $model,
                        $attribute,
                        Yii::t(
                            'abiturient/questionary/passport-data',
                            'Подсказка с ошибкой для поля "department_code" формы "Паспортные данные": `Код подразделения для паспорта РФ должен быть в виде 999-999`'
                        )
                    );
                }
            }
        }
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $key = $model->getIsNewRecord() ? 0 : $model->id;
        $russian_pass_ids = DocumentType::find()
            ->where(['ref_key' => Yii::$app->configurationManager->getCode('russian_passport_guid')])
            ->select('id')
            ->column();

        $russian_pass_ids = json_encode($russian_pass_ids);

        $errorMessage = json_encode(
            Yii::t(
                'abiturient/questionary/passport-data',
                'Подсказка с ошибкой для поля "department_code" формы "Паспортные данные": `Код подразделения для паспорта РФ должен быть в виде 999-999`'
            ),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
        return "
            var department_code = value;

            if (department_code.trim() === '') {
                return;
            }
            var doc_type = $('#passportdata-document_type_id_{$key}').val();
            if ({$russian_pass_ids}.includes(doc_type) && !department_code.match(/([0-9]{3}-[0-9]{3})/)) {
                messages.push('{$errorMessage}');
            }
        ";
    }
}
