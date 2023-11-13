<?php







use yii\helpers\Html;
use kartik\form\ActiveForm;

$this->title = "Изменение пароля";
$this->params['breadcrumbs'][] = $this->title;
?>

<?php if ($reason): ?>
<div class="alert alert-danger">
    <strong>Ошибка!</strong> <?php echo ' ' . $reason; ?>
</div>
<?php endif; ?>

<div class="site-reset-password">
    <h1><?php echo Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'change-password-form']); ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <?= $form->field($model, 'new_password')->passwordInput() ?>

            <?= $form->field($model, 'repeat_new_password')->passwordInput() ?>

            <div class="form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
