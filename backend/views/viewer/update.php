<?php

use backend\assets\AdmissionsCampaignManagerAsset;
use backend\models\ViewerAdmissionCampaignJunction;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

AdmissionsCampaignManagerAsset::register($this);

$this->title = $user->username;
$this->params['breadcrumbs'][] = ['label' => 'Управление приемными кампаниями модератора', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$newViewerAdmissionCampaignJunction = new ViewerAdmissionCampaignJunction();
$newViewerAdmissionCampaignJunction->user_id = $user->id;
$formName = mb_strtolower($newViewerAdmissionCampaignJunction->formName());

?>

<div class="user-index">

    <?php $form = ActiveForm::begin(); ?>

    <div class="checkbox">
        <label>
            <?php echo Html::checkbox('', false, ['value' => 'all', 'id' => 'manage_all_ac']); ?>
            Выбрать все приемные кампании
        </label>
    </div>

    <hr />

    <?php foreach ($applicationTypes as $applicationType) : ?>
        <div class="checkbox">
            <?php if (in_array($applicationType->id, $admissionCampaignJunctions)) : ?>
                <?php $newViewerAdmissionCampaignJunction->application_type_id = $applicationType->id; ?>
                <?php echo $form->field($newViewerAdmissionCampaignJunction, "[{$applicationType->id}]application_type_id")
                    ->checkbox(['value' => $applicationType->id])
                    ->label($applicationType->name); ?>
            <?php else : ?>
                <?php echo $form->field($newViewerAdmissionCampaignJunction, "[{$applicationType->id}]application_type_id")
                    ->checkbox(['value' => $applicationType->id])
                    ->label($applicationType->name); ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <p>
        <?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary']) ?>
    </p>

    <?php ActiveForm::end(); ?>

</div>