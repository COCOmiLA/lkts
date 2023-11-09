<?php

use common\components\AddressWidget\AddressWidget;
use common\components\AddressWidget\assets\AddressValidatorAsset;
use common\models\dictionary\Fias;
use common\models\relation_presenters\comparison\ComparisonHelper;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;















AddressValidatorAsset::register($this);

$appLanguage = Yii::$app->language;

$formName = $address_data->formNameForJs();
if (!isset($prefix)) {
    $prefix = '';
}

AddressWidget::addDataForJsItem(['prefix' => $prefix, 'formName' => $formName]);

$homeless_color_class = null;
$not_found_color_class = null;
$postal_index_color_class = null;
$flat_number_color_class = null;
$housing_number_color_class = null;
$house_number_color_class = null;
$street_name_color_class = null;
$town_name_color_class = null;
$city_name_color_class = null;
$area_name_color_class = null;
$region_name_color_class = null;
$country_name_color_class = null;
if ($comparison_helper) {
    [$_, $homeless_color_class] = $comparison_helper->getRenderedDifference('homeless');
    [$_, $not_found_color_class] = $comparison_helper->getRenderedDifference('not_found');
    [$_, $postal_index_color_class] = $comparison_helper->getRenderedDifference('postal_index');
    [$_, $flat_number_color_class] = $comparison_helper->getRenderedDifference('flat_number');
    [$_, $housing_number_color_class] = $comparison_helper->getRenderedDifference('housing_number');
    [$_, $house_number_color_class] = $comparison_helper->getRenderedDifference('house_number');
    [$_, $street_name_color_class] = $comparison_helper->getRenderedDifference('streetName');
    [$_, $town_name_color_class] = $comparison_helper->getRenderedDifference('townName');
    [$_, $city_name_color_class] = $comparison_helper->getRenderedDifference('cityName');
    [$_, $area_name_color_class] = $comparison_helper->getRenderedDifference('areaName');
    [$_, $region_name_color_class] = $comparison_helper->getRenderedDifference('regionName');
    [$_, $country_name_color_class] = $comparison_helper->getRenderedDifference('countryName');
}

$postal_index_required_classes = '';
$postalIndexRequiredClassesForLabel = '';
$showRequiredSymbols = $address_data->showRequiredSymbols();
if ($showRequiredSymbols) {
    
    $postal_index_required_classes = 'index-block';
    if ($address_data->isPostalIndexRequired()) {
        $postal_index_required_classes .= ' required';
        $postalIndexRequiredClassesForLabel = 'has-star';
    }
}

$addressWrapperId = "address-wrapper-id-{$prefix}";

?>

<div class="<?= $prefix ?>address-wrapper row" <?= $isReadonly ? 'data-readonly="1"' : '' ?> id="<?= $addressWrapperId ?>">
    <div class=" col-md-6">
        <div class="form-group <?= $homeless_color_class ?: '' ?>">
            <div class="row">
                <label class="col-md-9 col-12 col-form-label">
                    <?= $address_data->getAttributeLabel('homeless') ?>
                </label>
                <div class="col-md-3 col-12">
                    <?= $form->field($address_data, 'homeless', ['template' => $template])
                        ->label(false)
                        ->checkbox([
                            'label' => false,
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-homeless"
                        ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group <?= $showRequiredSymbols ? 'required' : '' ?> homeless-hide <?= $country_name_color_class ?: '' ?>">
            <div class="row">
                <label class="col-md-4 col-12 col-form-label <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $address_data->getAttributeLabel('country_id') ?>:
                </label>

                <div class="col-md-8 col-12">
                    <?= $form->field($address_data, 'country_id')
                        ->label(false)
                        ->widget(
                            Select2::class,
                            [
                                'language' => $appLanguage,
                                'data' => $countriesList,
                                'options' => [
                                    'placeholder' => Yii::t(
                                        'abiturient/questionary/block-address-data',
                                        'Подпись пустого значения выпадающего списка для поля "country_id" блока "Адрес" на странице анкеты поступающего: `Выберите страну ...`'
                                    ),
                                    'options' => $countryCodes,
                                    'id' => "{$prefix}{$formName}-country_id"
                                ],
                                'readonly' => $isReadonly,
                                'disabled' => !empty($disabled),
                                'pluginOptions' => [
                                    'dropdownParent' => "#{$addressWrapperId}",
                                    'allowClear' => false,
                                    'multiple' => false
                                ],
                                'pluginEvents' => ['change' => "function() { countryChecker('$prefix','$formName'); }"],
                            ]
                        ); ?>
                </div>
            </div>
        </div>

        <div class="form-group homeless-hide <?= $showRequiredSymbols ? 'required' : '' ?> <?= $region_name_color_class ?: '' ?>">
            <div class="row">
                <label class="col-md-4 col-12 col-form-label foreigner-hide <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $address_data->getAttributeLabel('region_id') ?>:
                </label>

                <label class="col-md-4 col-12 col-form-label foreigner-show <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $address_data->getAttributeLabel('region_id') ?>:
                </label>

                <div class="col-md-8 col-12">
                    <div class="notfound-hide foreigner-hide">
                        <?php echo $form->field($address_data, 'region_id')
                            ->label(false)
                            ->widget(
                                Select2::class,
                                [
                                    'language' => $appLanguage,
                                    'data' => ArrayHelper::map($region_selected, 'code', 'fullName'),
                                    'options' => [
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "region_id" блока "Адрес" на странице анкеты поступающего: `Выберите регион ...`'
                                        ),
                                        'class' => '',
                                        'id' => "{$prefix}{$formName}-region_id"
                                    ],
                                    'readonly' => $isReadonly,
                                    'disabled' => $isReadonly,
                                    'pluginOptions' => [
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "region_id" блока "Адрес" на странице анкеты поступающего: `Выберите регион`'
                                        ),
                                        'loadingText' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись загружающегося поля "region_id" блока "Адрес" на странице анкеты поступающего: `Загрузка ...`'
                                        ),
                                        'dropdownParent' => "#{$addressWrapperId}",
                                        'allowClear' => false,
                                        'multiple' => false,
                                        'ajax' => [
                                            'url' => Url::to(['/abiturient/region']),
                                            'method' => 'post',
                                            'delay' => '500',
                                            'dataType' => 'json',
                                            'data' => new JsExpression("function(params) {
                                                return {
                                                    filter_query:params.term,
                                                    page: params.page || 1
                                                }; 
                                            }")
                                        ],
                                    ],
                                ]
                            ); ?>
                    </div>

                    <div class="foreigner-show">
                        <?= Html::input(
                            'text',
                            'region-tmp',
                            '',
                            [
                                'class' => 'form-control',
                                'readonly' => true,
                                'disabled' => 'disabled',
                                'id' => "{$prefix}{$formName}-region_tmp"
                            ]
                        ); ?>
                    </div>

                    <div class="notfound-show foreigner-hide">
                        <?= $form->field($address_data, 'region_name', ['template' => $template])
                            ->label(false)
                            ->textInput([
                                'readonly' => $isReadonly,
                                'disabled' => !empty($disabled),
                                'id' => "{$prefix}{$formName}-region_name"
                            ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group homeless-hide <?= $area_name_color_class ?: '' ?>">
            <div class="row">
                <label class="col-md-4 col-12 col-form-label">
                    <?= $address_data->getAttributeLabel('area_id') ?>:
                </label>

                <div class="col-md-8 col-12">
                    <div class="notfound-hide foreigner-hide">
                        <?php echo $form->field($address_data, 'area_id')
                            ->label(false)
                            ->widget(
                                Select2::class,
                                [
                                    'language' => $appLanguage,
                                    'class' => 'form-control',
                                    'data' => ArrayHelper::map($area_selected, 'code', 'name'),
                                    'options' => [
                                        'id' => $prefix . 'area_id',
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "area_id" блока "Адрес" на странице анкеты поступающего: `Нет района`'
                                        )
                                    ],
                                    'readonly' => $isReadonly,
                                    'disabled' => $isReadonly,
                                    'pluginOptions' => [
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "area_id" блока "Адрес" на странице анкеты поступающего: `Нет района`'
                                        ),
                                        'loadingText' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись загружающегося поля "area_id" блока "Адрес" на странице анкеты поступающего: `Загрузка ...`'
                                        ),
                                        'dropdownParent' => "#{$addressWrapperId}",
                                        'allowClear' => true,
                                        'multiple' => false,
                                        'ajax' => [
                                            'url' => Url::to(['/abiturient/area']),
                                            'method' => 'post',
                                            'delay' => '500',
                                            'dataType' => 'json',
                                            'data' => new JsExpression("function(params) {
                                                var dep_parents = [
                                                    $('#{$prefix}{$formName}-region_id').val(),
                                                ];
                                                return {
                                                    filter_query:params.term,
                                                    depdrop_parents:dep_parents,
                                                    page: params.page || 1
                                                }; 
                                            }")
                                        ],
                                    ],
                                    'pluginEvents' => [
                                        'depdrop:change' => "
                                            function(event, id, value, count) {
                                                $(this).prop('disabled', false);
                                            }
                                        "
                                    ]
                                ]
                            ); ?>
                    </div>

                    <div class="foreigner-show">
                        <?= Html::input(
                            'text',
                            'area-tmp',
                            '',
                            [
                                'class' => 'form-control',
                                'readonly' => true,
                                'disabled' => 'disabled',
                                'id' => "{$prefix}{$formName}-area_tmp"
                            ]
                        ); ?>
                    </div>

                    <div class="notfound-show foreigner-hide">
                        <?= $form->field($address_data, 'area_name', ['template' => $template])
                            ->label(false)
                            ->textInput([
                                'readonly' => $isReadonly,
                                'disabled' => !empty($disabled),
                                'id' => "{$prefix}{$formName}-area_name"
                            ]); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group homeless-hide <?= $city_name_color_class ?: '' ?>">
            <div class="row">
                <label class="col-md-4 col-12 col-form-label">
                    <?= $address_data->getAttributeLabel('city_id') ?>:
                </label>

                <div class="col-md-8 col-12 notfound-hide">
                    <div class="foreigner-hide">
                        <?php echo $form->field($address_data, 'city_id')
                            ->label(false)
                            ->widget(
                                Select2::class,
                                [
                                    'language' => $appLanguage,
                                    'class' => 'form-control',
                                    'data' => ArrayHelper::map($city_selected, 'code', 'name'),
                                    'options' => [
                                        'id' => $prefix . 'city_id',
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "city_id" блока "Адрес" на странице анкеты поступающего: `Нет города`'
                                        )
                                    ],
                                    'readonly' => $isReadonly,
                                    'disabled' => $isReadonly,
                                    'pluginOptions' => [
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "city_id" блока "Адрес" на странице анкеты поступающего: `Нет города`'
                                        ),
                                        'loadingText' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись загружающегося поля "city_id" блока "Адрес" на странице анкеты поступающего: `Загрузка ...`'
                                        ),
                                        'dropdownParent' => "#{$addressWrapperId}",
                                        'allowClear' => true,
                                        'multiple' => false,
                                        'ajax' => [
                                            'url' => Url::to(['/abiturient/city']),
                                            'method' => 'post',
                                            'delay' => '500',
                                            'dataType' => 'json',
                                            'data' => new JsExpression("function(params) {
                                                var dep_parents = [
                                                    $('#{$prefix}area_id').val()
                                                ];
                                                var dep_params = [
                                                    $('#{$prefix}{$formName}-region_id').val(),
                                                ];
                                                return {
                                                    filter_query:params.term,
                                                    depdrop_parents:dep_parents,
                                                    depdrop_params:dep_params,
                                                    page: params.page || 1
                                                }; 
                                            }")
                                        ],
                                    ],
                                    'pluginEvents' => [
                                        'depdrop:change' => "
                                            function(event, id, value, count) {
                                                $(this).prop('disabled', false);
                                            }
                                        "
                                    ]
                                ]
                            ); ?>
                    </div>
                </div>

                <div class="col-md-8 col-12 notfound-show foreigner-show">
                    <?= $form->field($address_data, 'city_name', ['template' => $template])
                        ->label(false)
                        ->textInput([
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-city_name"
                        ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group homeless-hide <?= $town_name_color_class ?: '' ?>">
            <div class="row">
                <label class="col-md-4 col-12 col-form-label foreigner-hide">
                    <?= $address_data->getAttributeLabel('village_id') ?>:
                </label>

                <div class="col-md-8 col-12 notfound-hide">
                    <div class="foreigner-hide">
                        <?php echo $form->field($address_data, 'village_id')
                            ->label(false)
                            ->widget(
                                Select2::class,
                                [
                                    'language' => $appLanguage,
                                    'class' => 'form-control',
                                    'data' => ArrayHelper::map($village_selected, 'code', 'name'),
                                    'options' => [
                                        'id' => $prefix . 'village_id',
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "village_id" блока "Адрес" на странице анкеты поступающего: `Нет населенного пункта`'
                                        )
                                    ],
                                    'readonly' => $isReadonly,
                                    'disabled' => $isReadonly,
                                    'pluginOptions' => [
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "village_id" блока "Адрес" на странице анкеты поступающего: `Нет населенного пункта`'
                                        ),
                                        'loadingText' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись загружающегося поля "village_id" блока "Адрес" на странице анкеты поступающего: `Загрузка ...`'
                                        ),
                                        'allowClear' => true,
                                        'dropdownParent' => "#{$addressWrapperId}",
                                        'multiple' => false,
                                        'ajax' => [
                                            'url' => Url::to(['/abiturient/village']),
                                            'method' => 'post',
                                            'delay' => '500',
                                            'dataType' => 'json',
                                            'data' => new JsExpression("function(params) {
                                                var dep_parents = [
                                                    $('#{$prefix}area_id').val(),
                                                    $('#{$prefix}city_id').val()
                                                ];
                                                var dep_params = [
                                                    $('#{$prefix}{$formName}-region_id').val(),
                                                ];
                                                return {
                                                    filter_query:params.term,
                                                    depdrop_parents:dep_parents,
                                                    depdrop_params:dep_params,
                                                    page: params.page || 1
                                                }; 
                                            }")
                                        ],
                                    ],
                                    'pluginEvents' => [
                                        'depdrop:change' => "
                                            function(event, id, value, count) {
                                                $(this).prop('disabled', false);
                                            }
                                        "
                                    ]
                                ]
                            ); ?>
                    </div>
                </div>

                <div class="col-md-8 col-12 notfound-show foreigner-hide">
                    <?= $form->field($address_data, 'town_name', ['template' => $template])
                        ->label(false)
                        ->textInput([
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-town_name"
                        ]); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group homeless-hide <?= $street_name_color_class ?: '' ?>">
            <div class="row">
                <label class="col-4 col-md-5 col-form-label">
                    <?= $address_data->getAttributeLabel('street_id') ?>:
                </label>

                <div class="col-8 col-md-7 notfound-hide foreigner-hide">
                    <div>
                        <?php
                        $street_selected = Fias::find()->where([
                            'code' => $address_data->street_id,
                        ])->all();

                        echo $form->field($address_data, 'street_id')
                            ->label(false)
                            ->widget(
                                Select2::class,
                                [
                                    'language' => $appLanguage,
                                    'class' => 'form-control',
                                    'data' => ArrayHelper::map($street_selected, 'code', 'name'),
                                    'options' => [
                                        'id' => $prefix . 'street_id',
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "street_id" блока "Адрес" на странице анкеты поступающего: `Выберите улицу`'
                                        )
                                    ],
                                    'readonly' => $isReadonly,
                                    'disabled' => $isReadonly,
                                    'pluginOptions' => [
                                        'placeholder' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись пустого значения выпадающего списка для поля "street_id" блока "Адрес" на странице анкеты поступающего: `Выберите улицу`'
                                        ),
                                        'loadingText' => Yii::t(
                                            'abiturient/questionary/block-address-data',
                                            'Подпись загружающегося поля "street_id" блока "Адрес" на странице анкеты поступающего: `Загрузка ...`'
                                        ),
                                        'allowClear' => false,
                                        'dropdownParent' => "#{$addressWrapperId}",
                                        'multiple' => false,
                                        'ajax' => [
                                            'url' => Url::to(['/abiturient/street']),
                                            'method' => 'post',
                                            'delay' => '500',
                                            'dataType' => 'json',
                                            'data' => new JsExpression("function(params) {
                                                    var dep_parents = [
                                                        $('#{$prefix}city_id').val(),
                                                        $('#{$prefix}village_id').val()
                                                    ];
                                                    var dep_params = [
                                                        $('#{$prefix}{$formName}-region_id').val(),
                                                        $('#{$prefix}area_id').val()
                                                    ];
                                                    return {
                                                        filter_query:params.term,
                                                        depdrop_parents:dep_parents,
                                                        depdrop_params:dep_params,
                                                        page: params.page || 1
                                                    }; 
                                                }")
                                        ],
                                    ]
                                ]
                            ); ?>
                    </div>
                </div>

                <div class="col-8 col-md-7 notfound-show foreigner-show">
                    <?= $form->field($address_data, 'street_name', ['template' => $template])
                        ->label(false)
                        ->textInput([
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-street_name"
                        ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group <?= $showRequiredSymbols ? 'required' : '' ?> homeless-hide <?= $house_number_color_class ?: '' ?>">
            <div class="row">
                <label class="col-4 col-md-5 col-form-label <?= $showRequiredSymbols ? 'has-star' : '' ?>">
                    <?= $address_data->getAttributeLabel('house_number') ?>:
                </label>

                <div class="col-8 col-md-7">
                    <?= $form->field($address_data, 'house_number', ['template' => $template])
                        ->label(false)
                        ->textInput([
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-house_number"
                        ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group homeless-hide <?= $housing_number_color_class ?: '' ?>">
            <div class="row">
                <label class="col-4 col-md-5 col-form-label">
                    <?= $address_data->getAttributeLabel('housing_number') ?>:
                </label>

                <div class="col-8 col-md-7">
                    <?= $form->field($address_data, 'housing_number', ['template' => $template])
                        ->label(false)
                        ->textInput([
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-housing_number"
                        ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group homeless-hide <?= $flat_number_color_class ?: '' ?>">
            <div class="row">
                <label class="col-4 col-md-5 col-form-label">
                    <?= $address_data->getAttributeLabel('flat_number') ?>:
                </label>

                <div class="col-8 col-md-7">
                    <?= $form->field($address_data, 'flat_number', ['template' => $template])
                        ->label(false)
                        ->textInput([
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-flat_number"
                        ]); ?>
                </div>
            </div>
        </div>

        <div class="form-group homeless-hide <?= $postal_index_color_class ?: '' ?> <?php echo $postal_index_required_classes ?>">
            <div class="row">
                <label class="col-4 col-md-5 col-form-label <?= $postalIndexRequiredClassesForLabel ?>">
                    <?= $address_data->getAttributeLabel('postal_index') ?>:
                </label>

                <div class="col-8 col-md-7">
                    <?= $form->field($address_data, 'postal_index', ['template' => $template])
                        ->label(false)
                        ->textInput([
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-postal_index"
                        ]); ?>
                </div>
            </div>
        </div>

        <?php $displayCheckBox = Yii::$app->configurationManager->getCode('display_not_found_in_classifier'); ?>
        <div class="form-group homeless-hide <?= $not_found_color_class ?: '' ?>">
            <div class="row">
                <label style="<?= (($displayCheckBox == '1') ? 'display: none' : ''); ?>" class="col-md-9 col-12 col-form-label">
                    <?= $address_data->getAttributeLabel('not_found') ?>
                </label>

                <div style="<?= (($displayCheckBox == '1') ? 'display: none' : ''); ?>" class="col-md-3 col-12">
                    <?= $form->field($address_data, 'not_found', ['template' => $template])
                        ->label(false)
                        ->checkbox([
                            'label' => false,
                            'readonly' => $isReadonly,
                            'disabled' => !empty($disabled),
                            'id' => "{$prefix}{$formName}-not_found"
                        ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
