<?php

use common\models\settings\ApplicationsSettings;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;







$this->title = Yii::t('backend', 'Настройки подачи заявлений');
$this->params['breadcrumbs'][] = $this->title;

?>

<?php $form = ActiveForm::begin(); ?>

<?php foreach ($settings as $setting) : ?>
    <div class="row">
        <div class="col-12">
            <?php
            $type = $setting->type;
            ?>
            <?php if ($type === 'number') : ?>
                <?php echo $form->field($setting, "[{$setting->id}]value")
                    ->input('number'); ?>
            <?php elseif ($type === 'checkbox') : ?>
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
