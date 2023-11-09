<?php

use kartik\builder\TabularForm;
use kartik\form\ActiveForm;
use yii\helpers\Html;

$this->title = 'Настройки ссылок';

$form = ActiveForm::begin();

echo TabularForm::widget([
    'dataProvider' => $dataProvider,
    'form' => $form,
    'formName' => 'student_side_links',
    'attributes' => [
        'id' => [
            'label' => '',
            'type' => TabularForm::INPUT_HIDDEN
        ],
        'number' => ['type' => TabularForm::INPUT_TEXT],
        'description' => ['type' => TabularForm::INPUT_TEXT],
        'url' => ['type' => TabularForm::INPUT_TEXT]
    ],
    'actionColumn' => false,
    'serialColumn' => false,
    'staticOnly' => false,
    'gridSettings' => [
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm'],
        'striped' => false,
        'summary' => false,
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
        'condensed' => true,
        'panel' => [
            'heading' => false,
            'before' => false,
            'footer' => false,
            'after' => false
        ],
    ],
]); ?>

<div class="btn-group float-right">
    <?= Html::submitButton('Добавить', ['class' => 'btn btn-success kv-batch-create', 'name' => 'table_button_submit', 'value' => 'add']); ?>
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary kv-batch-save', 'name' => 'table_button_submit', 'value' => 'save']); ?>
    <?= Html::submitButton('Удалить', ['class' => 'btn btn-danger kv-batch-delete', 'name' => 'table_button_submit', 'value' => 'delete']); ?>
</div>

<?php ActiveForm::end();
