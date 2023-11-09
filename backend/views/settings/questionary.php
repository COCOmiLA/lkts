<?php

use common\modules\abiturient\models\questionary\QuestionarySettings;
use kartik\form\ActiveForm;
use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\web\View;







$this->title = Yii::t('settings/questionary', 'Настройки анкеты');

?>

<?php $form = ActiveForm::begin(); ?>

<?php foreach ($settings as $setting) : ?>
    <?php  ?>

    <div class="row">
        <div class="col-12">
            <?= $form->field($setting, "[{$setting->name}]value")
                ->widget(SwitchInput::class, [
                    'pluginOptions' => [
                        'onText' => Yii::t('settings/questionary', 'Да'),
                        'offText' => Yii::t('settings/questionary', 'Нет'),
                    ],
                ])
                ->label($setting->description) ?>
        </div>
    </div>
<?php endforeach; ?>

<?php echo Html::submitButton(
    Yii::t('settings/questionary', 'Сохранить'),
    ['class' => 'btn btn-primary']
); ?>

<?php ActiveForm::end();
