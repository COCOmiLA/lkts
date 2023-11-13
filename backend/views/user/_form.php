<?php

use common\models\User;
use kartik\form\ActiveForm;
use yii\helpers\Html;

if (!isset($id)) {
    $id = null;
}

?>

<?php if ($id && in_array(User::ROLE_ABITURIENT, $model->roles)) : ?>
    <div class="form-group">
        <?php echo Html::a(
            Yii::t('backend', 'Объединить физическое лицо'),
            ['/user/merge-individual', 'id' => $id],
            ['class' => 'btn btn-success']
        ) ?>
    </div>
<?php endif; ?>

<?php $form = ActiveForm::begin(); ?>

<?php echo $form->field($model, 'username') ?>

<?php echo $form->field($model, 'email')
    ->textInput(['type' => 'email']) ?>

<?php echo $form->field($model, 'password')
    ->passwordInput() ?>

<?php echo $form->field($model, 'status')
    ->label(Yii::t('backend', 'Активно'))
    ->checkbox() ?>

<?php echo $form->field($model, 'roles')
    ->checkboxList($roles) ?>

<div class="form-group">
    <?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
</div>

<?php ActiveForm::end();
