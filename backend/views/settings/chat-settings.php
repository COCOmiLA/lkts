<?php

use common\models\settings\ChatSettings;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;







$this->title = Yii::t('backend', 'Настройки чата');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $form = ActiveForm::begin(); ?>

<?php foreach ($settings as $setting) : ?>
    <?php  ?>
    <div class="row">
        <div class="col-12">
            <?php if ($setting->name === ChatSettings::PARAM_REQUEST_INTERVAL) : ?>
                <?php echo $form->field($setting, "[{$setting->id}]value")
                    ->input('number'); ?>
            <?php elseif ($setting->name === ChatSettings::ENABLE_CHAT) : ?>
                <?php echo $form->field($setting, "[{$setting->id}]value")
                    ->checkbox(); ?>
            <?php else : ?>
                <?php echo $form->field($setting, "[{$setting->id}]value")
                    ->textInput(); ?>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary float-right']); ?>

<?php ActiveForm::end();
