<?php

use backend\assets\AdmissionsCampaignManagerAsset;
use backend\models\ManageAC;
use kartik\form\ActiveForm;
use yii\helpers\Html;
use yii\web\View;






AdmissionsCampaignManagerAsset::register($this);

$this->title = $user->username;
$this->params['breadcrumbs'][] = ['label' => 'Управление приемными кампаниями модератора', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$newManageAc = new ManageAC();
$newManageAc->rbac_auth_assignment_user_id = $user->id;
$formName = mb_strtolower($newManageAc->formName());

?>

<?php $form = ActiveForm::begin(); ?>

<div class="checkbox">
    <label>
        <?php echo Html::checkbox('', false, ['value' => 'all', 'id' => 'manage_all_ac']); ?>
        Выбрать все приемные кампании
    </label>
</div>

<hr />

<?php foreach ($applicationType as $at) : ?>
    <div>
        <?php if (in_array($at->id, $arrayManageAc)) : ?>
            <?php $newManageAc->application_type_id = $at->id; ?>
            <?php echo $form->field($newManageAc, "[{$at->id}]application_type_id")
                ->checkbox(['value' => $at->id])
                ->label($at->name); ?>
        <?php else : ?>
            <?php echo $form->field($newManageAc, "[{$at->id}]application_type_id")
                ->checkbox(['value' => $at->id])
                ->label($at->name); ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<hr />

<div>
    <?php echo $form->field($managerAllowChat, 'allowChat')
        ->checkbox(); ?>
    <?php echo $form->field($managerAllowChat, 'nickname')
        ->label($managerAllowChat->getAttributeLabel('nickname') . ':'); ?>
    <?php echo $form->field($managerAllowChat, 'manager_id')
        ->hiddenInput()
        ->label(false); ?>
</div>

<hr />

<div>
    <div class="row">
        <div class="col">
            <?php echo $form->field($manager_notifications_configurator, 'notify_about_any_application_apply')
                ->checkbox(); ?>
            <?php echo $form->field($manager_notifications_configurator, 'notify_about_first_application_apply')
                ->checkbox(); ?>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <?php echo $form->field($manager_notifications_configurator, 'use_sms')
                ->checkbox(); ?>
            <?php echo $form->field($manager_notifications_configurator, 'phone')
                ->label($manager_notifications_configurator->getAttributeLabel('phone') . ':'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <?php echo $form->field($manager_notifications_configurator, 'use_email')
                ->checkbox(); ?>
            <?php echo $form->field($manager_notifications_configurator, 'email')
                ->textInput(['type' => 'email'])
                ->label($manager_notifications_configurator->getAttributeLabel('email') . ':'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <?php echo $form->field($manager_notifications_configurator, 'use_telegram')
                ->checkbox(); ?>
            <?php echo $form->field($manager_notifications_configurator, 'telegram_chat_id')
                ->label($manager_notifications_configurator->getAttributeLabel('telegram_chat_id') . ':'); ?>
        </div>
    </div>
</div>

<p>
    <?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary']) ?>
</p>

<?php ActiveForm::end();
