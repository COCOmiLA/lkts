<?php

use common\models\interfaces\IMainPageInstruction;
use kartik\form\ActiveForm;
use kartik\sortable\Sortable;
use yii\bootstrap4\Html;
use yii\web\View;








$form = ActiveForm::begin([
    'id' => 'sorted_element_form',
    'options' => ['enctype' => 'multipart/form-data'],
]);

$existInstructionItems = [];
foreach ($instructions as $model) {
    

    $partialPath = $model->getViewFileName();

    $existInstructionItems[] = ['content' => $this->render(
        $partialPath,
        compact([
            'form',
            'model',
        ])
    )];
}

?>

<div class="row">
    <div class="col-12">
        <?= Sortable::widget([
            'items' => $existInstructionItems,
            'options' => ['class' => 'instructions'],
            'pluginOptions' => ['acceptFrom' => implode(',', [
                '.instructions',
                '.instruction-step',
            ])]
        ]); ?>
    </div>
</div>

<?= $form->field($settingModel, 'sortableElements')
    ->hiddenInput()
    ->label(false) ?>

<div class="row">
    <div class="col-12">
        <?= Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary float-right']) ?>
    </div>
</div>

<?php ActiveForm::end();
