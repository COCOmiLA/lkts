<?php


namespace common\models\validators;

use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use Yii;
use yii\validators\Validator;






class SpecialityOlympiadValidator extends Validator
{
    




    public function validateAttribute($model, $attribute)
    {
        if (!empty($model->bachelorOlympiad)) {
            if (!empty($model->bachelorOlympiad->olympiad)) {
                if (
                    $model->speciality &&
                    $model->speciality->curriculumRef &&
                    $model->bachelorOlympiad->olympiad->getOlympiadFilters()
                        ->joinWith(['curriculumRef'])
                        ->andWhere([StoredCurriculumReferenceType::tableName() . '.reference_uid' => $model->speciality->curriculumRef->reference_uid])
                        ->exists()
                ) {
                    return;
                }
                $errorMessage = Yii::t(
                    'abiturient/bachelor/application/bachelor-speciality',
                    'Подсказка с ошибкой для поля "bachelor_olympiad_id" формы "НП": `Для указанного направления подготовки данная олимпиада не может быть учтена для приема без вступительных испытаний!`'
                );
                $model->addError($attribute, $errorMessage);
            }
        }
    }

    public function clientValidateAttribute($model, $attribute, $view)
    {
        $errorMessage = json_encode(
            Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'Подсказка с ошибкой для поля "bachelor_olympiad_id" формы "НП": `Для указанного направления подготовки данная олимпиада не может быть учтена для приема без вступительных испытаний!`'
            ),
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        return "
            if ($(this.input).find('option:selected').attr('data-not-matched-curriculum')) {
                messages.push({$errorMessage});
            }
        ";
    }
}
