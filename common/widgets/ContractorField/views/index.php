<?php

use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\dictionary\Contractor;
use common\widgets\ContractorField\assets\ContractorFieldAsset;
use common\widgets\ContractorField\ContractorField;
use kartik\widgets\Select2;
use yii\base\Model;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;



ContractorFieldAsset::register($this);

$blockIdFound = ContractorField::getBlockIdFound($model, $attribute, $keynum);
$blockIdNotFound = ContractorField::getBlockIdNotFound($model, $attribute, $keynum);
$contractor_type_input_id = ContractorField::getContractorTypeInputId($model, 'contractor-type', $keynum);
$wrapper_id= "contractor-field-wrapper-" . ContractorField::getIdentifier($model, $attribute, $keynum);
$config['approveModalId'] = $config['approveModalId'] ?? ContractorField::getIdentifier($model, "{$attribute}_approve_modal", $keynum);
?>

<div class="contractor-field-wrapper" id="<?php echo $wrapper_id ?>">
    <div id="<?php echo $blockIdFound ?>">
        <?php echo Html::hiddenInput('contractor_type_ref_uid', $contractor_type_ref_uid, [
            'id' => $contractor_type_input_id,
        ]); ?>
        <?php
        $item_selected = Contractor::find()->andWhere(['id' => $model->$attribute])->all();
        echo $form->field($model, $attribute)
            ->label($labels['contractor_name'])
            ->widget(Select2::class, [
                'language' => Yii::$app->language,
                'data' => ArrayHelper::map($item_selected, 'id', 'fullname'),
                'options' => [
                    'placeholder' => Yii::t(
                        'common/widgets/contractor-field',
                        'Подпись пустого значения выпадающего списка для поля "contractor_id" блока "Контрагент": `Выберите организацию ...`'
                    ),
                    'id' => $config['selectInputId'],
                    'data' => $config['data'] ?? [],
                ],
                'readonly' => $is_readonly,
                'disabled' => $disabled,
                'pluginOptions' => [
                    'allowClear' => false,
                    'multiple' => false,
                    'ajax' => [
                        'url' => Url::to(['/contractor/search']),
                        'method' => 'post',
                        'delay' => '500',
                        'dataType' => 'json',
                        'data' => new JsExpression("function(params) {
                            var type = $('#" . $contractor_type_input_id . "').val();
                            return {
                                q:params.term,
                                contractor_type:type,
                                page: params.page || 1
                            }; 
                        }")
                    ],
                    'dropdownParent' => "#{$wrapper_id}"
                ]
            ]); ?>

        <div class="alert alert-success contractor-approve-state">
            <?php echo Yii::t(
                'common/widgets/contractor-field', 
                'Сообщение о необходимости сохранить изменения после подтверждения контрагента: `Контрагент подтвержден. Необходимо сохранить данные формы.`'); 
            ?>
        </div>
    </div>
    <div id="<?php echo $blockIdNotFound ?>" style="display:none">
        <div class="form-group">
            <?php            
            echo Html::activeHiddenInput($new_contractor, 'contractor_type_ref_id', [
                'id' => ContractorField::getIdentifier($model, 'contractor_type_ref_id', $keynum),
                'name' => "{$config['contractorFormName']}[contractor_type_ref_id]"
            ]);
            echo $form->field($new_contractor, 'name')
                ->textInput([
                    'id' => $config['contractorTitleInputId'],
                    'placeholder' => Yii::t(
                        'common/widgets/contractor-field',
                        'Подсказка для поля "Наименование" формы "Контрагент": `Введите наименование организации`'
                    ),
                    'name' => "{$config['contractorFormName']}[name]",
                    'data' => [
                        'one-s-attribute-name' => RulesProviderByDocumentType::IssuedBy,
                        'skip_validation' => 1
                    ],
                    'readonly' => $is_readonly,
                    'disabled' => $disabled,
                ])
                ->label($labels['contractor_name']); ?>
        
            <?php echo $form->field($new_contractor, 'subdivision_code')->widget(
                MaskedInput::class,
                [
                    'mask' => $mask_subdivision_code,
                    'options' => [
                        'placeholder' => Yii::t(
                            'common/widgets/contractor-field',
                            'Подсказка для поля "Код подразделения" формы "Контрагент": `Введите код подразделения`'
                        ),
                        'id' => $config['contractorSubdivisionCodeInputId'],
                        'name' => "{$config['contractorFormName']}[subdivision_code]",
                        'class' => 'form-control',
                        'data' => [
                            'one-s-attribute-name' => RulesProviderByDocumentType::SubdivisionCode,
                            'skip_validation' => 1
                        ],
                        'readonly' => $is_readonly,
                        'disabled' => $disabled,
                    ],
                ]
            )->label($labels['subdivision_code']); ?>

            <div class="location-found">
                <?php echo $form->field($new_contractor, 'location_code')
                    ->label($labels['location_code'] ?? $new_contractor->getAttributeLabel('location_code'))
                    ->widget(
                        Select2::class,
                        [
                            'language' => Yii::$app->language,
                            'class' => 'form-control',
                            'options' => [
                                'id' => $config['contractorLocationCodeInputId'],
                                'name' => "{$config['contractorFormName']}[location_code]",
                                'placeholder' => Yii::t(
                                    'common/widgets/contractor-field',
                                    'Подпись пустого значения выпадающего списка для поля "location_code" формы "Контрагент": `Город`'
                                )
                            ],
                            'readonly' => $is_readonly,
                            'disabled' => $disabled,
                            'pluginOptions' => [
                                'placeholder' => Yii::t(
                                    'abiturient/questionary/block-address-data',
                                    'Подпись пустого значения выпадающего списка для поля "location_code" формы "Контрагент": `Город`'
                                ),
                                'loadingText' => Yii::t(
                                    'common/widgets/contractor-field',
                                    'Подпись загружающегося поля "location_code" формы "Контрагент": `Загрузка ...`'
                                ),
                                'allowClear' => true,
                                'multiple' => false,
                                'ajax' => [
                                    'url' => Url::to(['/contractor/location']),
                                    'method' => 'post',
                                    'delay' => '500',
                                    'dataType' => 'json',
                                    'data' => new JsExpression("function(params) {
                                        return {
                                            q:params.term,
                                            page: params.page || 1
                                        }; 
                                    }")
                                ],
                                'dropdownParent' => "#{$wrapper_id}"
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
            <div class="location-not-found" style="display: none;">
                <?php echo $form->field($new_contractor, 'location_name')->textInput([
                    'readonly' => $is_readonly,
                    'disabled' => $disabled,
                    'placeholder' => Yii::t(
                        'abiturient/questionary/block-address-data',
                        'Подпись пустого значения выпадающего списка для поля "location_code" формы "Контрагент": `Город`'
                    )
                ]); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->field($new_contractor, 'location_not_found', ['template' => '{input}'])
                ->checkbox([
                    'class' => 'contractor-location-not-found',
                    'readonly' => $is_readonly,
                    'disabled' => $disabled,
                    'id' => $config['locationNotFoundInputId'] ?? ContractorField::getIdentifier($model, 'location_not_found', $keynum),
                    'data' => [
                        'block-id' => $blockIdNotFound
                    ]
                ]); ?>
        </div>
    </div>
    
    <?php if ($moderator_allowed_to_edit): ?>
        <div class="form-group">
            <label for="not_found">
                <?php echo Yii::t(
                    'common/widgets/contractor-field',
                    'Подпись для поля "not_found" формы "Контрагент": `Не нашел организацию`'
                ); ?>
            </label>
            <?php
            echo Html::checkbox(Html::getInputName($model, $notFoundAttribute), false, [
                'id' => $config['notFoundCheckboxInputId'],
                'class' => 'select-widget-not-found',
                'readonly' => $is_readonly,
                'disabled' => $disabled,
                'data-block-id-found' => $blockIdFound,
                'data-block-id-not-found' => $blockIdNotFound,
            ]);
            ?>
        </div>
    <?php endif; ?>

    <?php if ($need_approve && $moderator_allowed_to_edit) : ?>
        <div class="form-group">
            <?php echo Html::a(Yii::t('common/widgets/contractor-field', 'Кнопка проверки контрагента: `Проверить`'), '#approveContragent', [
                'data-modal-id' => $config['approveModalId'],
                'class' => 'approve-modal-btn'
            ]); ?>
        </div>
        <?php echo $this->render('_approveModal', [
            'model' => $model,
            'attribute' => $attribute,
            'contractor' => $item_selected[0] ?? new Contractor(),
            'config' => $config,
            'contractor_type_input_id' => $contractor_type_input_id,
            'keynum' => $keynum,
            'contractor_type_ref_uid' => $contractor_type_ref_uid,
            'mask_subdivision_code' => $mask_subdivision_code,
            'found_block_id' => $blockIdFound,
        ]); ?>
    <?php endif; ?>
</div>