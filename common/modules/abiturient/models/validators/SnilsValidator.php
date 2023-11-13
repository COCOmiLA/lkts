<?php

namespace common\modules\abiturient\models\validators;

use Yii;
use yii\validators\Validator;

class SnilsValidator extends Validator
{
    public function init()
    {
        parent::init();
        $this->message = Yii::t(
            'abiturient/questionary/passport-data',
            'Подсказка с ошибкой для поля "snils" формы "Паспортные данные" при проверки фронтэндом: `Некорректный номер СНИЛС.`'
        );
    }

    public function validateAttribute($model, $attribute)
    {
        $snils = $model->$attribute;

        if (strpos($snils, '_') !== false && !preg_match('/___-___-___ __/', $snils)) {
            $this->addError(
                $model,
                'snils',
                Yii::t(
                    'abiturient/questionary/passport-data',
                    'Подсказка с ошибкой для поля "snils" формы "Паспортные данные" при проверки бекэндом: `Номер СНИЛС введен не полностью`'
                )
            );
            return false;
        }

        $snils = str_replace("-", "", $snils);
        $res = substr($snils, -2, 2);
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$snils[$i] * (9 - $i);
        }

        if ($sum > 101) {
            $sum %= 101;
        }
        if ($sum == 101 || $sum == 100) {
            $sum = 0;
        }
        if ($sum != (int)$res) {
            $this->addError($model, 'snils', $this->message);
            return false;
        }
        return true;
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        return <<<JS
            var snils = value;

            if (snils === "") {
                return;
            }

            snils = snils.replace(/[-\s]/g, "");
            var res = snils.substr(-2, 2);
            var sum = 0;

            for (var I = 0; I < 9; I++) {
                sum += parseInt(snils[I]) * (9 - I);
            }

            if (sum > 101) {
                sum = sum % 101;
            }

            if (sum == 101 || sum == 100) {
                sum = 0;
            }

            if (sum != +res) {
                messages.push({$message});
            }
JS;
    }
}
