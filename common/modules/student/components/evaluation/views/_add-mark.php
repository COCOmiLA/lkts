<?php

use kartik\widgets\DepDrop;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\form\ActiveForm;


$form = ActiveForm::begin([
    'action' => '/evaluation/mark',
    'options' => ['id' => 'add_marks_id'],
]);

?>

<div class="row">
    <div class="col-12">
        <?php echo $form->field($model, 'type')->dropDownList(
            $types,
            ['id' => 'mark-type'],
            [
                'template' => '{label}{input}',
                'options' => ['class' => 'form-group form-inline']
            ]
        ); ?>
    </div>

    <div class="col-12">
        <?php echo $form->field($model, 'mark')->widget(DepDrop::class, [
            'language' => 'ru',
            'name' => 'mark',
            'class' => 'form-control form-group',
            'data' => [],
            'options' => ['id' => 'mark_id', 'placeholder' => 'Выберите оценку'],
            'pluginOptions' => [
                'depends' => ['mark-type'],
                'placeholder' => 'Выберите оценку',
                'url' => Url::to(['/student/evaluation/mark-list']),
                'loadingText' => 'Загрузка ...',
                'initialize' => true,
            ],
        ]);

        echo Html::activeHiddenInput($model, 'luid');
        echo Html::activeHiddenInput($model, 'puid');
        echo Html::activeHiddenInput($model, 'uid');
        echo Html::activeHiddenInput($model, 'studentId');
        echo Html::activeHiddenInput($model, 'cafId');
        echo Html::activeHiddenInput($model, 'planId');
        echo Html::activeHiddenInput($model, 'statementId'); ?>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="form-group">
            <?= Html::submitButton('Установить', ['class' => 'btn btn-primary float-right']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end();
