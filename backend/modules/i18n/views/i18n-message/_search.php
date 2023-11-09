<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;




?>

<div class="i18n-message-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?php echo $form->field($model, 'id') ?>

    <?php echo $form->field($model, 'language') ?>

    <?php echo $form->field($model, 'translation') ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('backend', 'Поиск'), ['class' => 'btn btn-primary']) ?>
        <?php echo Html::resetButton(Yii::t('backend', 'Сбросить'), ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
