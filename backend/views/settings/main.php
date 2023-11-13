<?php

use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\web\View;






$this->title = Yii::t('settings/main', 'Общие настройки');

?>

<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-12">
        <?= $form->field($setting, "show_technical_info_on_error")
            ->widget(SwitchInput::class, [
                'pluginOptions' => [
                    'onText' => Yii::t('settings/main', 'Да'),
                    'offText' => Yii::t('settings/main', 'Нет'),
                ],
            ]); ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <?php echo Html::submitButton(
            Yii::t('settings/main', 'Сохранить'),
            ['class' => 'btn btn-primary']
        ); ?>
    </div>
</div>

<?php ActiveForm::end();
