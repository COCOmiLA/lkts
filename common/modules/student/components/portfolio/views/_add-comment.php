<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

$form = ActiveForm::begin([
    'action' => '/student/portfolio/comment',
    'options' => ['id' => 'add_comment_id'],
]); ?>

<div class="row">
    <div class="col-12">
        <?php echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
                echo $form->field($model, 'comment')->textarea();
            echo Html::endTag('div');
        echo Html::endTag('div');

        echo Html::activeHiddenInput($model, 'uid');
        echo Html::activeHiddenInput($model, 'luid');
        echo Html::activeHiddenInput($model, 'puid');
        echo Html::activeHiddenInput($model, 'studentId');
        echo Html::activeHiddenInput($model, 'recordbook_id'); ?>

        <div class="row">
            <div class="col-12">
                <div class="form-group">
                    <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary float-right']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end() ?>
