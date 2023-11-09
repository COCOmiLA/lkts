<?php

use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\Speciality;
use common\models\EmptyCheck;
use common\models\settings\SandboxSetting;
use common\modules\abiturient\assets\chosenApplicationAsset\ChosenApplicationAsset;
use common\modules\abiturient\models\bachelor\AdmissionAgreement;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\EducationData;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\depdrop\DepDrop;
use kartik\form\ActiveForm;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;






















$chosenSpeciality = $bachelor_speciality->speciality;
$appLanguage = Yii::$app->language;
$bachelor_speciality->setScenario(BachelorSpeciality::SCENARIO_FULL_VALIDATION);
$sandBoxEnabled = SandboxSetting::findOne(['name' => 'sandbox_enabled']);

ChosenApplicationAsset::register($this);

isset($isReadonly) || ($isReadonly = false);
isset($allowBenefitCategories) || ($allowBenefitCategories = true);

$is_manager = Yii::$app->user->identity->isModer();

$is_remove_spec_revertable = $bachelor_speciality->isDeleteRevertable();
$applicationCanEdit = $application->canEdit();
$canAddAgreements = $bachelor_speciality->canAddAgreements() && $bachelor_speciality->checkAgreementConditions();
$is_end_of_stage = !$is_remove_spec_revertable;
if ($is_manager) {
    $canEdit = !$isReadonly;
} else {
    $canEdit = $applicationCanEdit && !$is_end_of_stage;
}
$is_enlisted = false;
if ($bachelor_speciality->is_enlisted) {
    $canEdit = false;
    $isReadonly = true;
    $is_enlisted = true;
}

$panelId = "spec-panel-{$bachelor_speciality->id}";

$enrollmentPriority = ArrayHelper::getValue($bachelor_speciality, 'specialityPriority.enrollment_priority', 0);
$innerPriority = ArrayHelper::getValue($bachelor_speciality, 'specialityPriority.inner_priority', 0);

?>

<div
    class="card mb-3 spec-panel <?= isset($is_child) && $is_child ? 'mt-3' : '' ?>" id="<?= $panelId ?>"
    data-finance="<?php echo $chosenSpeciality->educationSourceRef->reference_uid; ?>"
    data-code="<?php echo str_replace('.', '', $chosenSpeciality->speciality_human_code); ?>"
    data-specid="<?php echo $chosenSpeciality->id; ?>"
    data-eduf="<?php echo $chosenSpeciality->educationFormRef->reference_uid; ?>"
    data-group="<?php echo $chosenSpeciality->competitiveGroupRef->reference_uid; ?>"
    data-dep="<?php echo $chosenSpeciality->subdivisionRef->reference_uid; ?>"
>
    <div class="card-header">
        <span class="badge badge-primary spec-priority">
            <?= $enrollmentPriority; ?>
        </span>

        <span>
            <strong>
                <?php echo $chosenSpeciality->getFullName($application->type); ?>
            </strong>

            <?php if (!EmptyCheck::isEmpty($bachelor_speciality->speciality->profileRef->reference_name ?? null)) {
                echo " ({$bachelor_speciality->speciality->profileRef->reference_name})";
            } ?>
        </span>

        <?php if ($bachelor_speciality->is_enlisted) : ?>
            <i class="fa fa-check small_verified_status super_centric"></i>
            <?php echo TooltipWidget::widget(
                ['message' => Yii::$app->configurationManager->getText('tooltip_for_bachelor_speciality_marked_as_enlisted')]
            ) ?>
        <?php endif; ?>

        <?php if (!$is_enlisted) : ?>
            <?php $removeBtn = Html::a(
                Yii::t(
                    'abiturient/bachelor/application/remove-speciality-modal',
                    'Подпись кнопки удаления НП; модального окна подтверждения удаления НП на странице НП: `Удалить`'
                ),
                null,
                [
                    'style' => 'margin-left: 10px;',
                    'data-id' => "{$bachelor_speciality->id}",
                    'class' => 'float-right remove-speciality btn btn-primary',
                ]
            );
            $cancelBtn = Html::button(
                Yii::t(
                    'abiturient/bachelor/application/remove-speciality-modal',
                    'Подпись кнопки отмены; модального окна подтверждения удаления НП на странице НП: `Отмена`'
                ),
                [
                    'data-dismiss' => 'modal',
                    'class' => 'btn btn-outline-secondary',
                ]
            );
            $removeSign = Html::a(
                '<i class="fa fa-remove" aria-hidden="true"></i>',
                null,
                [
                    'class' => 'float-right remove-speciality',
                    'data-id' => $bachelor_speciality->id,
                ]
            );

            $remove_spec_confirm_text = $is_remove_spec_revertable ?
                Yii::t(
                    'abiturient/bachelor/application/remove-speciality-modal',
                    'Текст сообщения; модального окна подтверждения обратимого удаления НП на странице НП: `Вы уверены, что хотите удалить направление?`'
                ) :
                Yii::t(
                    'abiturient/bachelor/application/remove-speciality-modal',
                    'Текст сообщения; модального окна подтверждения удаления НП на странице НП: `Вы уверены, что хотите удалить направление? Вы не сможете отменить данное действие. Прием заявлений по данному направлению прекращен.`'
                );
            ?>

            <?php if ($canEdit && !$is_child) : ?>
                <?php ?>
                <?php if (!$is_manager && $is_remove_spec_revertable) : ?>
                    <?= $removeSign; ?>
                <?php else : ?>
                    <?php Modal::begin([
                        'id' => "confirm_remove_speciality_modal_{$bachelor_speciality->id}",
                        'toggleButton' => [
                            'tag' => 'a',
                            'label' => '<i class="fa fa-remove" aria-hidden="true"></i>',
                            'class' => 'float-right', 'style' => 'cursor: pointer;',
                        ],
                        'title' => Yii::t(
                            'abiturient/bachelor/application/remove-speciality-modal',
                            'Заголовок модального окна подтверждения удаления НП на странице НП: `Подтверждение действия`'
                        ),
                        'footer' => $removeBtn . $cancelBtn,
                    ]); ?>

                    <p>
                        <?= $remove_spec_confirm_text ?>
                    </p>

                    <?php Modal::end(); ?>
                <?php endif; ?>

                <?php if ($enrollmentPriority > 1 || $innerPriority > 1) : ?>
                    <a href="" data-direction="up" data-id="<?= $bachelor_speciality->id; ?>"
                        class="float-right reorder-spec">
                        <i class="fa fa-arrow-up order-up-link" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>

                <?php if ($enrollmentPriority < $maxEnrollmentPriority || $innerPriority < $maxInnerPriority) : ?>
                    <a href="" data-direction="down" data-id="<?= $bachelor_speciality->id; ?>"
                        class="float-right reorder-spec">
                        <i class="fa fa-arrow-down order-down-link" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>                
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <p class="float-left">
                    <?= $chosenSpeciality->educationLevelRef->reference_name ?? ''; ?>
                    &nbsp
                </p>

                <p class="float-right">
                    <?= $chosenSpeciality->getAttributeLabel('finance_name') ?>:
                    <?= $chosenSpeciality->educationSourceRef->reference_name ?? ''; ?>
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <p class="float-left">
                    <?= $chosenSpeciality->getAttributeLabel('eduform_name') ?>:
                    <?= $chosenSpeciality->educationFormRef->reference_name ?? ''; ?>
                    &nbsp
                </p>

                <?php if ($bachelor_speciality->speciality->budgetLevelRef) : ?>
                    <p class="float-right">
                        <?= $chosenSpeciality->getAttributeLabel('budget_level_name') ?>:
                        <?= $chosenSpeciality->budgetLevelRef->reference_name; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <p class="float-left">
                    <?php
                    $faculty_name = $chosenSpeciality->subdivisionRef->reference_name ?? '';
                    $department_name = $chosenSpeciality->graduatingDepartmentName;
                    if ($faculty_name) {
                        if ($department_name) {
                            echo $faculty_name . ' (' . $department_name . ')';
                        } else {
                            echo $faculty_name;
                        }
                    } else {
                        echo $department_name;
                    }
                    ?>
                    &nbsp
                </p>

                <?php if ($bachelor_speciality->speciality->detailGroupRef) : ?>
                    <p class="float-right">
                        <?= $chosenSpeciality->getAttributeLabel('detail_group_name') ?>:
                        <?= $chosenSpeciality->detailGroupRef->reference_name; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if (isset($chosenSpeciality->competitiveGroupRef->reference_name)) : ?>
                    <p class="float-left">
                        <?= $chosenSpeciality->getAttributeLabel('group_name') ?>:
                        <?= $chosenSpeciality->competitiveGroupRef->reference_name; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php if (isset($chosenSpeciality->curriculumRef->reference_name)) : ?>
                    <p class="float-left">
                        <?= $chosenSpeciality->getAttributeLabel('curriculum_ref_id') ?>:
                        <?= $chosenSpeciality->curriculumRef->reference_name ?? ''; ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <?php
                $categories = $bachelor_speciality->getAvailableCategories($allowBenefitCategories);
                if ($bachelor_speciality->admissionCategory) {
                    
                    $filteredCategories = array_filter(
                        $categories,
                        function ($item) use ($bachelor_speciality) {
                            return $item->ref_key == $bachelor_speciality->admissionCategory->ref_key;
                        }
                    );
                    if (!$filteredCategories) {
                        $categories[] = $bachelor_speciality->admissionCategory;
                    }
                }
                $categories = ArrayHelper::map($categories, 'id', 'description');

                if (!$bachelor_speciality->isCommercialBasis()) {
                    echo Html::beginTag('div', ['class' => 'row']);
                    $categoryAll = AdmissionCategory::findByUID(Yii::$app->configurationManager->getCode('category_all'));
                    $id_category_all = $categoryAll->id;
                    $eduSource = $bachelor_speciality->speciality->educationSourceRef ?? null;

                    if (isset($eduSource) && $eduSource->reference_uid === BachelorSpeciality::getBudgetBasis()) {

                        echo Html::beginTag('div', ['class' => 'col-12 col-md-6']);

                        if ($bachelor_speciality->speciality->special_right) {
                            $cat = AdmissionCategory::findByUID(Yii::$app->configurationManager->getCode('category_specific_law'));
                            echo $form->field($bachelor_speciality, "[$key]admission_category_id")
                                ->dropDownList(
                                    $categories,
                                    [
                                        'id' => "cat_{$key}",
                                        'value' => $cat->id,
                                        'disabled' => 'disabled',
                                    ]
                                );
                            echo Html::activeHiddenInput($bachelor_speciality, "[$key]admission_category_id", [
                                'value' => $cat->id
                            ]);
                        } else {
                            echo $form->field($bachelor_speciality, "[$key]admission_category_id")
                                ->dropDownList($categories, [
                                    'id' => "cat_{$key}",
                                    'disabled' => $isReadonly,
                                    'prompt' => Yii::t(
                                        'abiturient/bachelor/application/chosen-application',
                                        'Подпись пустого значения для поля "admission_category_id"; в выбранном НП на странице НП: `Выберите ...`'
                                    ),
                                ]);
                        }

                        echo Html::endTag('div');

                        if ($allowBenefitCategories) {
                            echo Html::beginTag('div', ['class' => 'col-12 col-md-6 preference_wrapper']);
                            if (!$isReadonly) {
                                echo $form->field($bachelor_speciality, "[$key]preference_id")
                                    ->widget(
                                        DepDrop::class,
                                        [
                                            'language' => $appLanguage,
                                            'type' => DepDrop::TYPE_SELECT2,
                                            'options' => [
                                                'placeholder' => Yii::t(
                                                    'abiturient/bachelor/application/chosen-application',
                                                    'Подпись пустого значения для поля "preference_id"; в выбранном НП на странице НП: `Выберите ...`'
                                                ),
                                                'id' => "pref_drop_{$key}",
                                            ],
                                            'select2Options' => [
                                                'pluginOptions' => [
                                                    'dropdownParent' => "#{$panelId}",
                                                    'allowClear' => true,
                                                    'multiple' => false
                                                ]
                                            ],
                                            'pluginOptions' => [
                                                'placeholder' => Yii::t(
                                                    'abiturient/bachelor/application/chosen-application',
                                                    'Подпись пустого значения для поля "preference_id"; в выбранном НП на странице НП: `Выберите ...`'
                                                ),
                                                'loadingText' => Yii::t(
                                                    'abiturient/bachelor/application/chosen-application',
                                                    'Подпись загружающегося значения для поля "preference_id"; в выбранном НП на странице НП: `Загрузка ...`'
                                                ),
                                                'depends' => ["cat_{$key}"],
                                                'initialize' => true,
                                                'url' => Url::to(['bachelor/preference-list', 'app_id' => $application->id, 'id' => $bachelor_speciality->id]),
                                            ],
                                            'pluginEvents' => ['depdrop:change' => "
                                                function(event, id, value, count) {
                                                    if ($('#cat_{$key}').val() == '{$id_category_all}') {
                                                        $('#pref_drop_{$key}').prop('disabled', true).parents('.preference_wrapper').hide();
                                                    } else {
                                                        $('#pref_drop_{$key}').prop('disabled', false).parents('.preference_wrapper').show();
                                                    }
                                                }
                                            "]
                                        ]
                                    )
                                    ->label($bachelor_speciality->speciality->special_right ? BachelorSpeciality::getPreferenceFieldNameSpecialRight() : BachelorSpeciality::getPreferenceFieldName());
                            } else {
                                $pref = $bachelor_speciality->preference;
                                $show = [];
                                if ($pref !== null) {
                                    $show[$pref->id] = $pref->getName();
                                }
                                echo $form->field($bachelor_speciality, 'preference_id')
                                    ->dropDownList(
                                        $show,
                                        [
                                            'value' => array_keys($show)[0] ?? null,
                                            'prompt' => Yii::t(
                                                'abiturient/bachelor/application/chosen-application',
                                                'Подпись пустого значения для поля "preference_id"; в выбранном НП на странице НП: `Выберите ...`'
                                            ),
                                            'disabled' => $isReadonly
                                        ]
                                    );
                            }

                            echo Html::endTag('div');
                        }
                    }
                    if ($bachelor_speciality->speciality->isTargetReceipt()) {
                        echo Html::beginTag('div', ['class' => 'col-12']);

                        
                        $filtered_target_receptions = array_filter(($target_receptions ?? []),
                            function ($value, $key) use ($bachelor_speciality, $specialities) {
                                foreach ($specialities as $speciality) {
                                    if ($speciality->id != $bachelor_speciality->id && $speciality->speciality_id == $bachelor_speciality->speciality_id) {
                                        if ($speciality->target_reception_id == $key) {
                                            return false;
                                        }
                                    }
                                }
                                return true;
                            },
                            ARRAY_FILTER_USE_BOTH
                        );
                        echo $form->field($bachelor_speciality, "[$key]target_reception_id")
                            ->dropDownList($filtered_target_receptions, [
                                'id' => "tar_{$key}",
                                'disabled' => $isReadonly,
                                'prompt' => Yii::t(
                                    'abiturient/bachelor/application/chosen-application',
                                    'Подпись пустого значения для поля "target_reception_id"; в выбранном НП на странице НП: `Выберите ...`'
                                ),
                            ]);

                        echo Html::endTag('div');
                    }
                    echo Html::endTag('div');
                } ?>
                <?php if ($application->getBachelorPreferencesOlympForBVI()->exists()) : ?>
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <?php echo $form->field($bachelor_speciality, "[$key]is_without_entrance_tests")
                                ->checkbox([
                                    'id' => "is_without_entrance_tests_{$key}",
                                    'disabled' => $isReadonly,
                                ]); ?>
                        </div>

                        <div class="col-12 col-md-6 olympiads_wrapper"
                             style="<?= !$bachelor_speciality->is_without_entrance_tests ? 'display:none' : '' ?>">
                            <?php if (!$isReadonly) : ?>
                                <?php echo $form->field($bachelor_speciality, "[$key]bachelor_olympiad_id")
                                    ->widget(
                                        DepDrop::class,
                                        [
                                            'language' => $appLanguage,
                                            'type' => DepDrop::TYPE_SELECT2,
                                            'options' => [
                                                'placeholder' => Yii::t(
                                                    'abiturient/bachelor/application/chosen-application',
                                                    'Подпись пустого значения для поля "bachelor_olympiad_id"; в выбранном НП на странице НП: `Выберите ...`'
                                                ),
                                                'id' => "ol_drop_{$key}",
                                            ],
                                            'select2Options' => [
                                                'pluginOptions' => [
                                                    'dropdownParent' => "#{$panelId}",
                                                    'allowClear' => true,
                                                    'multiple' => false
                                                ]
                                            ],
                                            'pluginOptions' => [
                                                'placeholder' => Yii::t(
                                                    'abiturient/bachelor/application/chosen-application',
                                                    'Подпись пустого значения для поля "bachelor_olympiad_id"; в выбранном НП на странице НП: `Выберите ...`'
                                                ),
                                                'loadingText' => Yii::t(
                                                    'abiturient/bachelor/application/chosen-application',
                                                    'Подпись загружающегося значения для поля "bachelor_olympiad_id"; в выбранном НП на странице НП: `Загрузка ...`'
                                                ),
                                                'depends' => ["is_without_entrance_tests_{$key}"],
                                                'initialize' => true,
                                                'url' => Url::to(['bachelor/olympiads-list', 'app_id' => $application->id, 'id' => $bachelor_speciality->id]),
                                            ],
                                            'pluginEvents' => ['depdrop:change' => "
                                                function(event, id, value, count) {
                                                    if ($('#is_without_entrance_tests_{$key}').is(':checked')) {
                                                        $('#ol_drop_{$key}').prop('disabled', false).parents('.olympiads_wrapper').show();
                                                    } else {
                                                        $('#ol_drop_{$key}').prop('disabled', true).parents('.olympiads_wrapper').hide();
                                                    }
                                                }
                                            "]
                                        ]
                                    ) ?>
                            <?php else : ?>
                                <?php $olympiad = $bachelor_speciality->bachelorOlympiad;
                                $olympiad_show = [];
                                if ($olympiad !== null) {
                                    $olympiad_show[$olympiad->id] = $olympiad->getName();
                                }
                                echo $form->field($bachelor_speciality, 'bachelor_olympiad_id')
                                    ->dropDownList(
                                        $olympiad_show,
                                        [
                                            'value' => array_keys($olympiad_show)[0] ?? null,
                                            'prompt' => Yii::t(
                                                'abiturient/bachelor/application/chosen-application',
                                                'Подпись пустого значения для поля "preference_id"; в выбранном НП на странице НП: `Выберите ...`'
                                            ),
                                            'disabled' => true
                                        ]
                                    );
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$application->type->rawCampaign->common_education_document) : ?>
            <?= $this->render(
                '_select2_educations_data',
                [
                    'form' => $form,
                    'model' => $bachelor_speciality,
                    'attribute' => "[$key]educationsDataTagList",
                    'disabled' => $isReadonly,
                    'multiple' => !!$application->type->rawCampaign->allow_multiply_education_documents,
                    'data' => array_reduce(
                        $application->educations,
                        function ($carry, $edu) {
                            
                            $carry[$edu->id] = $edu->getDescriptionString();
                            return $carry;
                        },
                        []
                    ),
                ]
            ); ?>
        <?php endif; ?>

        <?php if ($application->type->campaign->consents_allowed) : ?>
            <div class="row">
                <div class="col-12">
                    <?php if ($bachelor_speciality->agreementDecline) : ?>
                        <div class="agreement_decline">
                            <?php if (isset($bachelor_speciality->agreementDecline->linkedFile)) : ?>
                                <?php $url = Url::to(['site/download-agreement-decline', 'id' => $bachelor_speciality->agreementDecline->id]) ?>
                                <a class="btn btn-danger" target="_blank" href="<?= $url; ?>">
                                    <i class="fa fa-download" aria-hidden="true"></i>
                                    <?= Yii::t(
                                        'abiturient/bachelor/application/chosen-application',
                                        'Подпись ссылки на скачивание заявления на отказ на согласие; в выбранном НП на странице НП: `Отзыв согласия`'
                                    ) ?>
                                </a>
                            <?php else : ?>
                                <span class="btn btn-danger" disabled>
                                    <?= Yii::t(
                                        'abiturient/bachelor/application/chosen-application',
                                        'Подпись ссылки на скачивание заявления на отказ на согласие; в выбранном НП на странице НП: `Отзыв согласия`'
                                    ) ?>
                                </span>
                            <?php endif; ?>

                            <?php if (
                                $applicationCanEdit && $canAddAgreements && !$is_manager &&
                                !$bachelor_speciality->agreementDecline->isSentTo1C && $bachelor_speciality->agreementDecline->agreementToDelete
                            ) : ?>
                                <a class="btn btn-outline-secondary agreement-decline-remove" href="#"
                                   data-toggle="modal" data-target="#agreementDeclineRemoveModal"
                                   data-id="<?= $bachelor_speciality->agreementDecline->id; ?>">
                                    <i class="fa fa-undo" aria-hidden="true"></i>
                                    <?= Yii::t(
                                        'abiturient/bachelor/application/chosen-application',
                                        'Подпись кнопки отзыва отказа на согласие; в выбранном НП подтверждения удаления НП на странице НП: `Отменить отказ`'
                                    ) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="admission-agree">
                        <?php if (!$bachelor_speciality->agreement) : ?>
                            <?php if ($applicationCanEdit && $canAddAgreements && !$is_manager) : ?>
                                <?php if ($application->haveAttachedAgreementExcludeNonBudget() && !$bachelor_speciality->isCommercialBasis()) : ?>
                                    <?php $tooltipTitle = Yii::t(
                                        'abiturient/bachelor/application/chosen-application',
                                        'Текст выплывающей подсказки для кнопки прикрепления согласия; в выбранном НП на странице НП: `Невозможно прикрепить согласие на зачисление, так как в системе уже есть информация о прикрепленном согласии на зачисление. Для подачи нового согласия на зачисление отзовите предыдущее.`'
                                    ) ?>
                                    <a href="" onclick="disableHref(event)" data-toggle="tooltip"
                                       title="<?php echo $tooltipTitle ?>">
                                        <?= Yii::t(
                                            'abiturient/bachelor/application/agreement-modal',
                                            'Подпись кнопки открытия модального окна согласия; на странице НП: `Прикрепить согласие на зачисление`'
                                        ) ?>
                                    </a>
                                <?php elseif ($application->educations) : ?>
                                    <a class="add-agree" href="#" data-toggle="modal" data-target="#agreementModal"
                                       data-id="<?= $bachelor_speciality->id; ?>"
                                       data-code="<?= ArrayHelper::getValue($application->user, 'userRef.reference_id'); ?>">
                                        <?= Yii::t(
                                            'abiturient/bachelor/application/agreement-modal',
                                            'Подпись кнопки открытия модального окна согласия; на странице НП: `Прикрепить согласие на зачисление`'
                                        ) ?>
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else : ?>
                            <?php $isVerified = $bachelor_speciality->agreement->status === AdmissionAgreement::STATUS_VERIFIED; ?>
                            <div class="agreement-panel alert <?= ($isVerified || $is_manager) ? 'alert-success' : 'alert-warning' ?>">
                                <div class="agreement-status">
                                    <?php if (!$isVerified) : ?>
                                        <?php if ($is_manager) : ?>
                                            <span>
                                                <?= Yii::t(
                                                    'abiturient/bachelor/application/chosen-application',
                                                    'Информирующий текст, показывающий что согласие прикреплено; в выбранном НП на странице НП: `Прикреплено согласие на зачисление.`'
                                                ) ?>
                                            </span>
                                        <?php else : ?>
                                            <span>
                                                <?= Yii::t(
                                                    'abiturient/bachelor/application/chosen-application',
                                                    'Информирующий текст, поясняющий дальнейшие действия после прикрепления согласия; в выбранном НП на странице НП: `Согласие не подтверждено ПК. После прикрепления согласия на зачисление необходимо нажать на кнопку "{action}" для передачи согласия в приемную комиссию.`',
                                                    ['action' => $application->getApplyText()]
                                                ) ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else : ?>
                                        <span>
                                            <?= Yii::t(
                                                'abiturient/bachelor/application/chosen-application',
                                                'Информирующий текст, подтверждающий что согласие принято в ПК; в выбранном НП на странице НП: `Согласие подтверждено ПК.`'
                                            ) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="agreement-actions">
                                    <?php if (isset($bachelor_speciality->agreement->linkedFile)) : ?>
                                        <?php $url = Url::to(['site/downloadagreement', 'id' => $bachelor_speciality->agreement->id]) ?>
                                        <a target="_blank" class="btn btn-primary" href="<?= $url; ?>">
                                            <i class="fa fa-download" aria-hidden="true"></i>
                                            <?= Yii::t(
                                                'abiturient/bachelor/application/chosen-application',
                                                'Подпись ссылки на скачивание прикопленного ранее согласия; в выбранном НП на странице НП: `Скачать`'
                                            ) ?>
                                            <?= TooltipWidget::widget([
                                                'message' => Yii::$app->configurationManager->getText('download_consent_tooltip'),
                                                'params' => 'style="margin-left: 4px;"'
                                            ]) ?>
                                        </a>
                                    <?php else : ?>
                                        <span class="btn btn-primary" disabled>
                                            <?= Yii::t(
                                                'abiturient/bachelor/application/chosen-application',
                                                'Информирующий текст, показывающий что согласие прикреплено; в выбранном НП на странице НП: `Согласие на зачисление`'
                                            ) ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($applicationCanEdit && $canAddAgreements) : ?>
                                        <?php if (($isVerified || $hasVerifiedAgreements) && !$is_manager) : ?>
                                            <?php echo Html::tag(
                                                'a',
                                                '<i class="fa fa-remove" aria-hidden="true"></i>' .
                                                Yii::t(
                                                    'abiturient/bachelor/application/agreement-decline-modal',
                                                    'Подпись кнопки открытия модального окна отзыва согласия; на странице НП: `Отозвать`'
                                                ),
                                                [
                                                    'class' => "decline-agree btn btn-danger",
                                                    'href' => "#",
                                                    'data-toggle' => "modal",
                                                    'style' => "margin-left: 10px;",
                                                    'data-target' => "#agreementDeclineModal",
                                                    'data-id' => $bachelor_speciality->agreement->id,
                                                    'disabled' => !($applicationCanEdit && $canAddAgreements)
                                                ]
                                            ) ?>
                                        <?php elseif (!$is_manager) : ?>
                                            <?php echo Html::tag(
                                                'a',
                                                '<i class="fa fa-remove" aria-hidden="true"></i>' .
                                                Yii::t(
                                                    'abiturient/bachelor/application/agreement-decline-non-verified-modal',
                                                    'Подпись кнопки открытия модального окна отзыва не подтверждённого согласия; на странице НП: `Отозвать`'
                                                ),
                                                [
                                                    'class' => "decline-agree btn btn-danger",
                                                    'href' => "#",
                                                    'data-toggle' => "modal",
                                                    'style' => "margin-left: 10px;",
                                                    'data-target' => "#agreementDeclineNonVerifiedModal",
                                                    'data-id' => $bachelor_speciality->agreement->id,
                                                    'disabled' => !($applicationCanEdit && $canAddAgreements)
                                                ]
                                            ) ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($bachelor_speciality->agreementRecords) : ?>
                <div class="row">
                    <div class="col-12">
                        <ul class="list-group">
                            <?php foreach ($bachelor_speciality->agreementRecords as $agreement_record) : ?>
                                <li class="list-group-item"><?php echo "{$agreement_record->getTypeDescription()} {$agreement_record->getFormattedDate()}" ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($bachelor_speciality->isFullCostRecovery() && !EmptyCheck::isEmpty($bachelor_speciality->paid_contract_guid)) : ?>
            <?php $attached_contract = $bachelor_speciality->getAttachedPaidContract(); ?>
            <?php if (!empty($attached_contract)) : ?>
                <div class="row">
                    <div class="col-12">
                        <div class="agreement-panel alert alert-success">
                            <div class="agreement-status">
                                <span>
                                    <?= Yii::t(
                                        'abiturient/bachelor/application/chosen-application',
                                        'Информирующий текст, показывающий что договор прикреплён; в выбранном НП на странице НП: `Приложен договор об оказании платных образовательных услуг`'
                                    ) ?>
                                </span>
                            </div>

                            <div class="agreement-actions">
                                <?php if (isset($attached_contract->linkedFile)) : ?>
                                    <?php $url = Url::to(['/bachelor/download-attached-paid-contract', 'id' => $bachelor_speciality->id]) ?>
                                    <a target="_blank" class="btn btn-primary" href="<?= $url; ?>">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                        <?= Yii::t(
                                            'abiturient/bachelor/application/chosen-application',
                                            'Подпись ссылки на скачивание прикрепленного договора; в выбранном НП на странице НП: `Скачать`'
                                        ) ?>
                                    </a>
                                <?php else : ?>
                                    <span class="btn btn-primary" disabled>
                                        <?= Yii::t(
                                            'abiturient/bachelor/application/chosen-application',
                                            'Подпись кнопки показывающей что договор есть а прикрепленного файла нет; в выбранном НП на странице НП: `Договор`'
                                        ) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (!$is_manager && ($canEdit || $canAddAgreements)) : ?>
                                    <?php $url = Url::to(['/bachelor/remove-attached-paid-contract', 'id' => $bachelor_speciality->id]) ?>
                                    <a class="btn btn-danger" href="<?= $url ?>">
                                        <i class="fa fa-remove" aria-hidden="true"></i>
                                        <?= Yii::t(
                                            'abiturient/bachelor/application/chosen-application',
                                            'Подпись ссылки на отзыва договора; на странице НП: `Отозвать`'
                                        ) ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif (!$is_manager && ($canEdit || $canAddAgreements)) : ?>
                <div class="row">
                    <div class="col-12">
                        <?= Html::a(
                            Yii::t(
                                'abiturient/bachelor/application/paid-contract-modal',
                                'Подпись кнопки открытия модального окна договора; на странице НП: `Прикрепить договор об оказании платных образовательных услуг`'
                            ),
                            null,
                            [
                                'class' => 'add-paid-modal-opener',
                                'style' => 'cursor: pointer;',
                                'data' => [
                                    'spec-id' => $bachelor_speciality->id,
                                    'guid' => $bachelor_speciality->paid_contract_guid,
                                ],
                            ]
                        ); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($bachelor_speciality->is_enlisted): ?>
            <div class="row">
                <div class="col-12">
                    <?php if (!empty($bachelor_speciality->enrollmentRejectionAttachments)): ?>
                        <?php 
                            $enrollment_rejection_attachments = $bachelor_speciality->enrollmentRejectionAttachments;
                            $rejection_attachment = array_pop($enrollment_rejection_attachments);
                        ?>
                        <div class="agreement-panel alert alert-warning">
                            <div class="agreement-status">
                                <span>
                                    <i class="fa fa-ban" aria-hidden="true"></i>
                                    <?= Yii::t(
                                        'abiturient/bachelor/application/chosen-application',
                                        'Информирующий текст, показывающий что подан отказ от зачисления; в выбранном НП на странице НП: `Подан отказ от зачисления.`'
                                    , ['action' => $application->getApplyText()]) ?>
                                </span>                                
                            </div>
                            <div class="agreement-actions">
                                <?php $url = Url::to(['site/download', 'id' => $rejection_attachment->id]) ?>
                                <a target="_blank" class="btn btn-primary" href="<?= $url; ?>">
                                    <i class="fa fa-download" aria-hidden="true"></i>
                                    <?= Yii::t(
                                        'abiturient/bachelor/application/chosen-application',
                                        'Подпись ссылки на скачивание прикопленного ранее отказа от зачисления; в выбранном НП на странице НП: `Скачать`'
                                    ) ?>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <?= Html::a(
                            Yii::t(
                                'abiturient/bachelor/application/chosen-application',
                                'Подпись кнопки открытия модального окна договора; на странице НП: `Прикрепить отказ от зачисления`'
                            ),
                            '#',
                            [
                                'data-toggle' => 'modal',
                                'data-target' => "#reject-enrollment-modal-{$bachelor_speciality->id}",
                            ]
                        ); ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        if ($children_bachelor_speciality) {
            foreach ($children_bachelor_speciality as $child_key => $child) {
                echo $this->render(
                    '@common/modules/abiturient/views/partial/application/_chosen_application',
                    ArrayHelper::merge(
                        compact(
                            'application',
                            'specialities',
                            'form',
                            'display_speciality_name',
                            'display_group_name',
                            'display_code',
                            'isReadonly',
                            'target_receptions',
                            'allowBenefitCategories',
                            'maxEnrollmentPriority',
                            'maxInnerPriority',
                        ),
                        [
                            'key' => $child_key,
                            'is_child' => true,
                            'bachelor_speciality' => $child,
                            'children_bachelor_speciality' => [],
                        ]
                    )
                );
            }
        }
        ?>
    </div>
</div>