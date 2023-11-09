<?php

use kartik\form\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin(); ?>

<?php echo $form->field($model, 'name') ?>

<?php echo $form->field($model, 'campaign_id')->dropDownList($campaigns); ?>

<?php echo $form->field($model, 'hide_ege')->checkbox(); ?>

<?php echo $form->field($model, 'hide_benefits_block')->checkbox(); ?>

<?php echo $form->field($model, 'hide_olympic_block')->checkbox(); ?>

<?php echo $form->field($model, 'hide_targets_block')->checkbox(); ?>

<?php echo $form->field($model, 'disable_type')->checkbox(); ?>

<?php echo $form->field($model, 'hide_ind_ach')->checkbox(); ?>

<?php echo $form->field($model, 'enable_check_ege')->checkbox(); ?>

<?php echo $form->field($model, 'hide_scans_page')->checkbox(); ?>

<?php echo $form->field($model, 'minify_scans_page')->checkbox(); ?>

<?php echo $form->field($model, 'hide_profile_field_for_education')->checkbox(); ?>

<?php echo $form->field($model, 'allow_add_new_education_after_approve')->checkbox(); ?>

<?php echo $form->field($model, 'allow_add_new_file_to_education_after_approve')->checkbox(); ?>

<?php echo $form->field($model, 'allow_delete_file_from_education_after_approve')->checkbox(); ?>

<?php echo $form->field($model, 'display_speciality_name')->checkbox(); ?>

<?php echo $form->field($model, 'display_group_name')->checkbox(); ?>

<?php echo $form->field($model, 'display_code')->checkbox(); ?>

<?php echo $form->field($model, 'can_see_actual_address')->checkbox(); ?>

<?php echo $form->field($model, 'required_actual_address')->checkbox(); ?>

<?php echo $form->field($model, 'moderator_allowed_to_edit')->checkbox(); ?>

<?php echo $form->field($model, 'persist_moderators_changes_in_sent_application')->checkbox(); ?>

<?php echo $form->field($model, 'archive_actual_app_on_update')->checkbox(); ?>

<?php echo $form->field($model, 'filter_spec_by_spec')->checkbox(); ?>

<?php echo $form->field($model, 'filter_spec_by_dep')->checkbox(); ?>

<?php echo $form->field($model, 'filter_spec_by_code')->checkbox(); ?>

<?php echo $form->field($model, 'filter_spec_by_eduf')->checkbox(); ?>

<?php echo $form->field($model, 'filter_spec_by_fin')->checkbox(); ?>

<?php echo $form->field($model, 'filter_spec_by_detail_group')->checkbox(); ?>

<?php echo $form->field($model, 'filter_spec_by_special_law')->checkbox(); ?>

<?php if ($model->campaign) {
    echo $form->field($model->campaign, 'snils_is_required')
        ->checkbox(['disabled' => true]);
} ?>
<?php if ($model->campaign) {
    echo $form->field($model->campaign, 'consents_allowed')
        ->checkbox(['disabled' => true]);
} ?>
<?php if ($model->campaign) {
    echo $form->field($model->campaign, 'multiply_applications_allowed')
        ->checkbox(['disabled' => true]);
} ?>

<?php echo $form->field($model, 'allow_special_requirement_selection')->checkbox(); ?>

<?php echo $form->field($model, 'allow_language_selection')->checkbox(); ?>

<?php echo $form->field($model, 'allow_pick_dates_for_exam')->checkbox(); ?>

<?php echo $form->field($model, 'can_change_date_exam_from_1c')->checkbox(); ?>

<?php echo $form->field($model, 'enable_autofill_specialty_on_a_universal_basis')->checkbox(); ?>

<?php echo $form->field($model, 'citizenship_is_required')->checkbox(); ?>

<?php echo $form->field($model, 'show_list')
    ->hiddenInput()
    ->label(false); ?>

<?php echo $form->field($model, 'allow_remove_sent_application_after_moderation')->checkbox(); ?>

<?php echo $form->field($model, 'allow_secondary_apply_after_approval')->checkbox(); ?>

<?php echo $form->field($model, 'disable_creating_draft_if_exist_sent_application')->checkbox(); ?>

<?php if ($model->campaign) {
    echo $form->field($model->campaign, 'allow_multiply_education_documents')
        ->checkbox(['disabled' => true]);
} ?>

<?php if ($model->campaign) {
    echo $form->field($model->campaign, 'common_education_document')
        ->checkbox(['disabled' => true]);
} ?>

<?php if ($model->campaign) {
    echo $form->field($model->campaign, 'separate_statement_for_full_payment_budget')
        ->checkbox(['disabled' => true]);
} ?>

<div class="form-group">
    <?php echo Html::submitButton(Yii::t('backend', 'Сохранить'), ['class' => 'btn btn-primary', 'name' => 'addscan-button']) ?>
</div>

<?php ActiveForm::end();
