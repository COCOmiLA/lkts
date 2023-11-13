<?php

use common\models\UserProfile;
use kartik\form\ActiveForm;
use yii\helpers\Html;





$this->title = Yii::t('backend', 'Редактировать профиль')
?>

<div class="user-profile-form card-body">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'firstname')->textInput(['maxlength' => 255]) ?>

    <?php echo $form->field($model, 'middlename')->textInput(['maxlength' => 255]) ?>

    <?php echo $form->field($model, 'lastname')->textInput(['maxlength' => 255]) ?>

    <?php echo $form->field($model, 'locale')->dropDownlist(Yii::$app->localizationManager->getAvailableLocales(true)) ?>

    <?php echo $form->field($model, 'gender')->dropDownlist([
        UserProfile::GENDER_FEMALE => Yii::t('backend', 'Женский'),
        UserProfile::GENDER_MALE => Yii::t('backend', 'Мужской')
    ]) ?>

    <div class="form-group">
        <?php echo Html::submitButton(Yii::t('backend', 'Редактировать'), ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
