<?php

use backend\models\CleanLog;
use backend\models\SystemLog;
use backend\models\SystemLogInfo;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\bootstrap4\Html;
use yii\web\View;





$action = 'delete-logs';
$classes = [
    SystemLog::class,
    SystemLogInfo::class,
]

?>

<div class="card mb-3 card-body">
    <div class="card-header">
        <h4>
            <?php echo Yii::t('backend', 'Очистка системных логов'); ?>
        </h4>
    </div>

    <div class="card-body" id="card-body-id">
        <?php foreach ($classes as $class) : ?>
            <?php $model = new CleanLog();
            $model->className = $class; ?>

            <?php if (!empty((int) $model->count)) : ?>
                <?php $form = ActiveForm::begin(['action' => $action]) ?>

                <?php echo $form->field($model, 'className')
                    ->hiddenInput(['id' => "{$model->clientFormName}-{$model->index}-className"])
                    ->label(false) ?>

                <div class="row">
                    <div class="col-12">
                        <?php echo $form->field($model, 'numberToDelete')
                            ->widget(
                                Select2::class,
                                [
                                    'data' => $model->numbersToDeleteList,
                                    'options' => [
                                        'id' => "{$model->clientFormName}-{$model->index}-numberToDelete",
                                        'placeholder' => Yii::t('backend', 'Выберите ...'),
                                    ],
                                    'pluginOptions' => [
                                        'tags' => true,
                                        'maximumInputLength' => 10,
                                        'tokenSeparators' => [',', ' '],
                                        'dropdownParent' => '#card-body-id',
                                    ],
                                ]
                            ); ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <?= Html::submitButton(
                            Yii::t('backend', 'Очистить'),
                            ['class' => 'btn btn-danger']
                        ) ?>
                    </div>
                </div>

                <?php ActiveForm::end() ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>