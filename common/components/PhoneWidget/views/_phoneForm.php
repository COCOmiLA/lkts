<?php

use common\components\PhoneWidget\assets\PhoneFormAssets;
use common\components\PhoneWidget\PhoneWidget;
use common\modules\abiturient\models\PersonalData;
use kartik\form\ActiveForm;
use yii\web\View;
use yii\widgets\MaskedInput;















PhoneFormAssets::register($this);

$this->registerJsVar('citizenId', $citizenId);

if (!isset($fieldConfig)) {
    $fieldConfig = [];
}

?>

<div class="row">
    <div class="col-12">
        <?= PhoneWidget::renderField(
            $form,
            $personalData,
            $phoneField,
            $fieldConfig,
            [
                'class' => MaskedInput::class,
                'config' => [
                    'mask' => [$phoneNumberMask],
                    'clientOptions' => ['clearMaskOnLostFocus' => true, 'greedy' => false],
                    'options' => [
                        'placeholder' => '+7(999)9999999',
                        'readonly' => $isReadonly,
                        'disabled' => !empty($disabled),
                        'data-mask' => $phoneNumberMask,
                        'class' => 'form-control phone_code_field',
                    ],
                ]
            ]
        ); ?>
    </div>
</div>