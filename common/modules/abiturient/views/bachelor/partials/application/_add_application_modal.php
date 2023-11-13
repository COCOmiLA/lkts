<?php

use common\models\dictionary\Speciality;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\views\bachelor\assets\AddApplicationModalAsset;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\form\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\web\View;


















AddApplicationModalAsset::register($this);
$modelIdentification = rand(0, 1000);

?>

<div class="modal fade bd-example-modal-lg" id="<?= $addApplicationModalId ?>" tabindex="-1" role="dialog" aria-labelledby="<?= $addApplicationModalId ?>Label">
    <div class="modal-dialog modal-lg specialities-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">
                    <?= $cardHeader ?>
                    <?= TooltipWidget::widget([
                        'message' => Yii::$app->configurationManager->getText('choose_specialities_tooltip', $application->type ?? null)
                    ]) ?>
                </h4>

                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php ActiveForm::begin(['action' => ['/bachelor/add-specialities', 'id' => $application->id]]); ?>

            <input
                class="speciality-order d-none"
                name="spec_order"
                type="hidden"
            >

            <div class="modal-body">
                <div class="row">
                    <?php $show_filter_spec = ArrayHelper::getValue($application, 'type.filter_spec_by_spec', true); ?>
                    <div class="col-6" style="<?php echo !$show_filter_spec ? 'display:none;' : '' ?>">
                        <?php $fieldSearchSpecialityPlaceholder = Yii::t(
                            'abiturient/bachelor/application/application-modal',
                            'Подпись для фильтра направлений модального окна добавления НП на странице НП: `Направление подготовки`'
                        ) ?>
                        <input placeholder="<?= Html::encode($fieldSearchSpecialityPlaceholder) ?>" class="form-control spec-search-input" type="text" name="SearchSpeciality" id="SearchSpeciality--<?= $modelIdentification; ?>" data-model_identification="<?= $modelIdentification; ?>" />
                    </div>

                    <?php $show_filter_code = ArrayHelper::getValue($application, 'type.filter_spec_by_code', true); ?>
                    <div class="col-6" style="<?php echo !$show_filter_code ? 'display:none;' : '' ?>">
                        <?php $fieldSearchCodePlaceholder = Yii::t(
                            'abiturient/bachelor/application/application-modal',
                            'Подпись для фильтра по шифрам специальности модального окна добавления НП на странице НП: `Шифр специальности`'
                        ) ?>
                        <input placeholder="<?= Html::encode($fieldSearchCodePlaceholder) ?>" class="form-control spec-search-input" type="text" name="SearchCode" id="SearchCode--<?= $modelIdentification; ?>" data-model_identification="<?= $modelIdentification; ?>" />
                    </div>
                </div>

                <div class="row">
                    <?php $show_filter_dep = ArrayHelper::getValue($application, 'type.filter_spec_by_dep', true); ?>
                    <div class="col-4" style="<?php echo !$show_filter_dep ? 'display:none;' : '' ?>">
                        <?= Html::dropDownList(
                            'search-dep',
                            '',
                            $department_array,
                            [
                                'class' => 'form-control spec-search-select',
                                'prompt' => Yii::t(
                                    'abiturient/bachelor/application/application-modal',
                                    'Подпись пустого значения выпадающего списка для фильтра подразделений модального окна добавления НП на странице НП: `Подразделение`'
                                ),
                                'id' => "search-dep--{$modelIdentification}",
                                'data-model_identification' => $modelIdentification,
                            ]
                        ); ?>
                    </div>

                    <?php $show_filter_eduf = ArrayHelper::getValue($application, 'type.filter_spec_by_eduf', true); ?>
                    <div class="col-4" style="<?php echo !$show_filter_eduf ? 'display:none;' : '' ?>">
                        <?= Html::dropDownList(
                            'search-eduf',
                            '',
                            $eduform_array,
                            [
                                'class' => 'form-control spec-search-select',
                                'prompt' => Yii::t(
                                    'abiturient/bachelor/application/application-modal',
                                    'Подпись пустого значения выпадающего списка для фильтра по форме обучения модального окна добавления НП на странице НП: `Форма обучения`'
                                ),
                                'id' => "search-eduf--{$modelIdentification}",
                                'data-model_identification' => $modelIdentification,
                            ]
                        ); ?>
                    </div>

                    <?php $show_filter_fin = ArrayHelper::getValue($application, 'type.filter_spec_by_fin', true); ?>
                    <div class="col-4" style="<?php echo !$show_filter_fin ? 'display:none;' : '' ?>">
                        <?= Html::dropDownList(
                            'search-fin',
                            '',
                            $finance_array,
                            [
                                'class' => 'form-control spec-search-select',
                                'prompt' => Yii::t(
                                    'abiturient/bachelor/application/application-modal',
                                    'Подпись пустого значения выпадающего списка для фильтра по форме оплаты модального окна добавления НП на странице НП: `Форма оплаты`'
                                ),
                                'id' => "search-fin--{$modelIdentification}",
                                'data-model_identification' => $modelIdentification,
                                'style' => 'margin-right:0;'
                            ]
                        ); ?>
                    </div>
                </div>
                <div class="row">
                    <?php $show_filter_spec = ArrayHelper::getValue($application, 'type.filter_spec_by_detail_group', true) && Yii::$app->configurationManager->getCode('special_quota_detail_group_guid'); ?>
                    <div class="col-6" style="<?php echo !$show_filter_spec ? 'display:none;' : '' ?>">
                        <?= Html::dropDownList(
                            'search-detail_group',
                            '',
                            $detail_groups_array,
                            [
                                'class' => 'form-control spec-search-select',
                                'prompt' => Yii::t(
                                    'abiturient/bachelor/application/application-modal',
                                    'Подпись пустого значения выпадающего списка для фильтра по особенностям приёма модального окна добавления НП на странице НП: `Особенность приёма`'
                                ),
                                'id' => "search-detail_group--{$modelIdentification}",
                                'data-model_identification' => $modelIdentification,
                                'style' => 'margin-right:0;'
                            ]
                        ); ?>
                    </div>

                    <?php $show_filter_spec = ArrayHelper::getValue($application, 'type.filter_spec_by_special_law', true); ?>
                    <div class="col-6" style="<?php echo !$show_filter_spec ? 'display:none;' : '' ?>">
                        <?= Html::dropDownList(
                            'search-special_law',
                            '',
                            [
                                '0' => Yii::t('abiturient/bachelor/application/application-modal', 'Название элемента фильтра для получения всех направлений без особого права: `Без особого права`'),
                                '1' => Yii::t('abiturient/bachelor/application/application-modal', 'Название элемента фильтра для получения всех направлений с особым правом: `С особым правом`'),
                            ],
                            [
                                'class' => 'form-control spec-search-select',
                                'prompt' => Yii::t(
                                    'abiturient/bachelor/application/application-modal',
                                    'Подпись пустого значения выпадающего списка для фильтра по особому праву модального окна добавления НП на странице НП: `Наличие особого права`'
                                ),
                                'id' => "search-special_law--{$modelIdentification}",
                                'data-model_identification' => $modelIdentification,
                                'style' => 'margin-right:0;'
                            ]
                        ); ?>
                    </div>
                </div>
                <div style="clear: both;"></div>

                <div class="speciality-container pre-scrollable">
                    <?php if (isset($available_specialities) && $available_specialities) : ?>
                        <?php foreach ($available_specialities as $available_specialty) : ?>
                            <?php  ?>

                            <?php $uid = $available_specialty->educationSourceRef->reference_uid ?? null;
                            if (isset($financialBasisTypeFilter) && !in_array($uid, $financialBasisTypeFilter)) {
                                continue;
                            } ?>

                            <?= $this->render(
                                '_add_application_modal_panel',
                                [
                                    'displayCode' => $display_code,
                                    'displayGroupName' => $display_group_name,
                                    'availableSpecialty' => $available_specialty,
                                    'modelIdentification' => $modelIdentification,
                                    'displaySpecialityName' => $display_speciality_name,
                                ]
                            ) ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <div class="alert alert-info" role="alert">
                            <?= Yii::t(
                                'abiturient/bachelor/application/application-modal',
                                'Текст сообщения для пустого списка направлений; модального окна добавления НП на странице НП: `Нет доступных направлений.`'
                            ) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <?= Yii::t(
                        'abiturient/bachelor/application/application-modal',
                        'Подпись кнопки отмены добавления НП; модального окна добавления НП на странице НП: `Отмена`'
                    ) ?>
                </button>

                <?php $globalTextForSubmitTooltip = Yii::$app->configurationManager->getText('global_text_for_submit_tooltip', $application->type ?? null); ?>
                <?= Html::submitButton(
                    Yii::t(
                        'abiturient/bachelor/application/application-modal',
                        'Подпись кнопки добавления НП; модального окна добавления НП на странице НП: `Добавить`'
                    ),
                    [
                        'value' => Yii::t(
                            'abiturient/bachelor/application/application-modal',
                            'Подпись кнопки добавления НП; модального окна добавления НП на странице НП: `Добавить`'
                        ),
                        'class' => 'btn btn-primary anti-clicker-btn btn-in-popup',
                        'data-tooltip_title' => $globalTextForSubmitTooltip,
                    ]
                ); ?>
            </div>
            <?php ActiveForm::end() ?>
        </div>
    </div>
</div>