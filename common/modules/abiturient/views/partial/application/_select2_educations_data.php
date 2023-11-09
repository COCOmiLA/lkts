<?php

use common\models\interfaces\CanUseMultiplyEducationDataInterface;
use common\modules\abiturient\assets\applicationAsset\Select2EducationsAsset;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\web\View;












Select2EducationsAsset::register($this);

?>

<div class="row">
    <div class="col-12">
        <?= $form->field($model, $attribute)
            ->widget(Select2::class, [
                'data' => $data,
                'maintainOrder' => true,
                'options' => [
                    'disabled' => $disabled,
                    'multiple' => $multiple,
                    'class' => 'select2-educations',
                    'placeholder' => Yii::t(
                        'abiturient/bachelor/application/chosen-application',
                        'Подпись пустого значения для поля "education_id"; в выбранном НП на странице НП: `Выберите ...`'
                    ),
                ],
                'pluginOptions' => [
                    'tags' => true,
                    'tokenSeparators' => [BachelorSpeciality::EDUCATIONS_DATA_TAG_LIST_SEPARATOR],
                ],
            ]); ?>
    </div>
</div>