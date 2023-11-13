<?php

use common\models\User;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;







$this->title = Yii::t('backend', 'Объединить физическое лицо');

?>

<h4>
    <?= Yii::t('backend', 'Объединить физическое лицо') ?>
</h4>

<?php $form = ActiveForm::begin(); ?>

<?= $form->field($user, 'guid')
    ->label($user->username) ?>

<div class="form-group">
    <?= Html::submitButton(
        Yii::t('backend', 'Сохранить'),
        ['class' => 'btn btn-primary', 'name' => 'signup-button']
    ) ?>
</div>

<?php ActiveForm::end();
