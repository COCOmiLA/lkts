<?php

use common\models\settings\ChangeHistorySettings;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;







$this->title = Yii::t('backend', 'Настройки окна просмотра истории изменений');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $form = ActiveForm::begin(); ?>

<?php foreach ($settings as $setting) : ?>
    <?php  ?>
    <div class="row">
        <div class="col-12">
            <?php if (in_array($setting->name, ChangeHistorySettings::PARAM_REQUEST_UNSIGNED_INT)) : ?>
                <?php echo $form->field($setting, "[{$setting->id}]value")
                    ->input('number'); ?>
            <?php else : ?>
                <?php echo $form->field($setting, "[{$setting->id}]value")
                    ->textInput(); ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary float-right']); ?>

<?php ActiveForm::end();
