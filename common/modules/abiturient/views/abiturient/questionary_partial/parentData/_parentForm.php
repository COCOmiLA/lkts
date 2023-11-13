<?php

use common\components\AddressWidget\AddressWidget;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\parentData\ParentAddressData;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\models\parentData\ParentPassportData;
use common\modules\abiturient\models\parentData\ParentPersonalData;
use yii\helpers\Html;
use yii\web\View;
use kartik\form\ActiveForm;







?>

<?php $template = "{input}\n{error}"; ?>

<?php

$disabled = '';
if (!$canEdit) {
    $disabled = 'disabled';
    $isReadonly = true;
}

$formId = "form-parent-" . (int)$model->id;
$form = ActiveForm::begin(['id' => $formId, 'action' => $action, 'options' => ['class' => 'document-form form-parent', 'data-id' => (int)$model->id]]);

?>

<div class="row">
    <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>

    <div class="col-12 div-parent-errors"></div>

    <?= $this->render('_parentPersonalData', [
        'form' => $form,
        'model' => $model,
        'personal_data' => $model->personalData ?? new ParentPersonalData(),
        'familyTypes' => $familyTypes,
        'template' => $template,
        'keynum' => $keynum,
        'isReadonly' => $isReadonly,
        'disabled' => $disabled,
    ]); ?>

    <?php if (!\Yii::$app->configurationManager->getParentDataSetting('hide_address_data_block')) : ?>
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h4>
                        <?= Yii::t(
                            'abiturient/questionary/parent-modal',
                            'Заголовок блока "Адрес родителя" модального окна на странице анкеты поступающего: `Адрес постоянной регистрации`'
                        ); ?>
                    </h4>
                </div>

                <div class="card-body">
                    <?php $addressData = $model->addressData ?? new ParentAddressData(); ?>
                    <?= AddressWidget::widget([
                        'template' => $template,
                        'form' => $form,
                        'isReadonly' => $isReadonly,
                        'disabled' => $disabled,
                        'addressData' => $addressData,
                        'prefix' => $addressData->getInputPrefix()
                    ]) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!\Yii::$app->configurationManager->getParentDataSetting('hide_passport_data_block')) : ?>
        <?= $this->render('_parentPassportData', [
            'form' => $form,
            'canEdit' => $canEdit,
            'model' => $model->passportData ?? new ParentPassportData(),
            'passportTypes' => $passportTypes,
            'document_type' => $document_type,
            'keynum' => $model->passportData ? $model->passportData->id : 0,
            'application' => $application ?? null,
        ]); ?>
    <?php endif; ?>

    <div class="col-12 div-parent-errors"></div>

    <?php if ($canEdit) : ?>
        <div class="col-12">
            <?= Html::button(
                Yii::t(
                    'abiturient/questionary/parent-modal',
                    'Подпись кнопки сохранения родительских данных в модальном окне на странице анкеты поступающего: `Сохранить`'
                ),
                [
                    'class' => 'btn btn-primary float-right btn-save-parent-data submit-emulation',
                    'data-id' => (int)$model->id,
                    'data-loading-text' => Yii::t(
                        'abiturient/questionary/parent-modal',
                        'Подпись для обрабатываемого запроса сохранения родительских данных в модальном окне на странице анкеты поступающего: `Сохранение...`'
                    ),
                ]
            ); ?>
        </div>
    <?php endif; ?>

    <?php ActiveForm::end(); ?>
</div>

<?php AddressWidget::registerJsVarForInitialization("parentAddressWidgetDataForInitialization");
