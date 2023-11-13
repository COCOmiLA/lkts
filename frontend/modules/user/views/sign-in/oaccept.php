<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;





$this->title = "Приложение запрашивает разрешение на использование вашей идентификационной информации";
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <h4><?php echo Html::encode($this->title) ?></h4>
    <br />
    <div class="row">
        <div class="col-lg-5">
            <?php $form = ActiveForm::begin(['id' => 'oaccept-form']); ?>
                    <?php echo Html::hiddenInput('accept', '1', ['id' => 'accept-param']); ?>
                <div class="form-group">
                    <?php echo Html::submitButton('Разрешить', ['class' => 'btn btn-primary', 'name' => 'oaccept-button']); ?>
                    <?php echo Html::submitButton('Отмена', ['class' => 'btn btn-outline-secondary', 'onclick' => '$("#accept-param").val("0");']); ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
