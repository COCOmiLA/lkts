<?php

use common\models\dictionary\DocumentType;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$documentTypesOptions = [];

if ($model->document_type_ref_id && !isset($document_types[$model->document_type_ref_id])) {
    [
        'description' => $description,
        'documentTypesOptions' => $documentTypesOptions,
    ] = DocumentType::processArchiveDocForDropdown('id', $model->document_type_ref_id);
    $document_types[$model->document_type_ref_id] = $description;

    if ($documentTypesOptions) {
        $model->addError(
            'document_type_ref_id',
            Yii::t(
                'backend',
                'Внимание! Выбранный элемент "{attribute}" находится в архиве.',
                ['attribute' => $model->getAttributeLabel('document_type_ref_id')]
            )
        );
    }
}

$form = ActiveForm::begin();

echo $form->field($model, 'document_type_ref_id')
    ->dropDownList($document_types, ['options' => $documentTypesOptions]);

echo $form->field($model, 'scan_required')
    ->checkbox(['label' => 'Обязательно для прикрепления']);

echo $form->field($model, 'admission_campaign_ref_id')
    ->dropDownList($campaigns);

?>

<div class="form-group">
    <?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'addscan-button']) ?>
</div>

<?php ActiveForm::end();
