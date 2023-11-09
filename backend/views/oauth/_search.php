<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;




?>

<div class="user-search card-body">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?php echo $form->field($model, 'client_id') ?>

    <?php echo $form->field($model, 'client_secret') ?>

    <?php echo $form->field($model, 'redirect_uri') ?>

    <?php echo $form->field($model, 'grant_types') ?>

    <?php echo $form->field($model, 'scope') ?>

    <?php echo $form->field($model, 'user_id') ?>


    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('backend', 'Поиск'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::resetButton(Yii::t('backend', 'Сбросить'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
