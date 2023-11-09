<?php

use common\models\dictionary\StoredReferenceType\StoredContractorTypeReferenceType;
use common\widgets\ContractorField\ContractorField;
use kartik\select2\Select2;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;

Modal::begin([
    'size' => 'modal-md',
    'id' => $config['approveModalId'],
    'options' => [
        'tabindex' => false,
    ],
    'closeButton' => false, 
    'title' => Html::tag('h4', Yii::t(
        'common/widgets/contractor-field',
        'Заголовок модального окна проверки контрагента: `Подтверждение контрагента`'
    )) . Html::button('<span aria-hidden="true">×</span>', [
        'class' => 'close close-approve-contractor-modal',
    ]),
]);
?>

<?php $approve_block_id =  ContractorField::getIdentifier($model, "{$attribute}_contractor_approve", $keynum); ?>
<?php $bind_block_id =  ContractorField::getIdentifier($model, "{$attribute}_contractor_bind", $keynum); ?>
<div id="<?php echo $approve_block_id ?>" class="contractor-apporve-wrapper">
    <?php echo Html::hiddenInput('approve_contractor_id', $contractor->id); ?>
    <div class="row">
        <div class="col-12">
            <div class="contractor-approve-error" style="display:none">
                <div class="alert alert-danger">
                    <?php echo Yii::t(
                        'common/widgets/contractor-field',
                        'Сообщение об ошибке в модальном окне подтверждения контрагента: `Произошла ошибка`'
                    ); ?>
                </div>
            </div>
            <div class="form-group approve-contractor-type required">
                <label for="approve_contractor_name">
                    <?php echo $contractor->getAttributeLabel('contractor_type_ref_id'); ?>
                </label>
                <?php echo Select2::widget([
                    'name' => 'approve_contractor_type_ref_uid',
                    'value' => $contractor->contractorTypeRef->reference_uid ?? $contractor_type_ref_uid,
                    'language' => Yii::$app->language,
                    'data' => ArrayHelper::map(StoredContractorTypeReferenceType::findAll(['archive' => false]), 'reference_uid', 'reference_name'),
                    'options' => [
                        'placeholder' => Yii::t(
                            'common/widgets/contractor-field',
                            'Подпись пустого значения выпадающего списка для поля "contractor_id" блока "Контрагент": `Выберите организацию ...`'
                        ),
                        'id' => ContractorField::getIdentifier($model, "approve_{$attribute}_contractor_type_ref_uid", $keynum),
                        'class' => 'approve-contractor-type-field',
                    ],
                    'pluginOptions' => [
                        'allowClear' => false,
                        'multiple' => false,
                        'dropdownParent' => "#{$approve_block_id}"
                    ]
                ]); ?>
                <div class="invalid-feedback">Необходимо заполнить это поле.</div>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group required">
                <label for="approve_contractor_name">
                    <?php echo $contractor->getAttributeLabel('name'); ?>
                </label>
                <?php echo Html::textInput('approve_contractor_name', $contractor->name, [
                    'class' => 'form-control'
                ]); ?>
            </div>
        </div>
        <div class="col-12">
            <label for="approve_contractor_subdivision_code">
                <?php echo $contractor->getAttributeLabel('subdivision_code'); ?>
            </label>
            <div class="form-group required">
                <?php echo MaskedInput::widget(
                    [
                        'mask' => $mask_subdivision_code,
                        'value' => $contractor->subdivision_code,
                        'name' => 'approve_contractor_subdivision_code',
                        'options' => [
                            'class' => 'form-control'
                        ],
                    ]
                );?>
            </div>
        </div>
        <div class="col-12 location-found">
            <label for="approve_contractor_location_code">
                <?php echo $contractor->getAttributeLabel('location_code'); ?>
            </label>
            <div class="form-group">
                <?php echo Select2::widget(
                        [
                            'language' => Yii::$app->language,
                            'class' => 'form-control',
                            'data' => ArrayHelper::map([$contractor->location], 'code', 'fullname'),
                            'value' => $contractor->location_code,
                            'name' => "approve_contractor_location_code",
                            'options' => [
                                'placeholder' => Yii::t(
                                    'common/widgets/contractor-field',
                                    'Подпись пустого значения выпадающего списка для поля "location_code" формы "Контрагент": `Нет города`'
                                ),
                                'id' => ContractorField::getIdentifier($model, "approve_{$attribute}_location_code", $keynum),
                            ],
                            'readonly' => false,
                            'disabled' => false,
                            'pluginOptions' => [
                                'placeholder' => Yii::t(
                                    'abiturient/questionary/block-address-data',
                                    'Подпись пустого значения выпадающего списка для поля "location_code" формы "Контрагент": `Нет города`'
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
                                'dropdownParent' => "#{$approve_block_id}"
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
        <div class="col-12 location-not-found">
            <label for="approve_contractor_location_code">
                <?php echo $contractor->getAttributeLabel('location_code'); ?>
            </label>
            <div class="form-group">
                <?php echo Html::textInput('approve_contractor_location_name', $contractor->location_name, [
                    'class' => 'form-control'
                ]); ?>
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                <?php echo Html::checkbox('approve_contractor_location_not_found', $contractor->location_not_found, [
                    'label' => $contractor->getAttributeLabel('location_not_found'),
                    'class' => 'contractor-location-not-found',
                    'data' => [
                        'block-id' => $approve_block_id
                    ]
                ]); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <?php echo Html::button(
                    Yii::t('common/widgets/contractor-field', 'Подпись кнопки для сохранения формы; модального окна подтверждения контрагента: `Сохранить`'), 
                    [
                        'class' => 'btn btn-primary float-right approve-new-contractor-btn',
                        'data-found-block-id' => $found_block_id, 
                        'data-approve-block-id' => $approve_block_id,
                        'data-target-input-id' => $config['selectInputId'],
                        'data-modal-id' => $config['approveModalId'],
                        'data-entity-class' => get_class($model),
                        'data-entity-attribute' => $attribute,
                        'data-entity-id' => $model->getPrimaryKey(),
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>


<p>
    <?php echo Yii::t(
        'common/widgets/contractor-field',
        'Подсказка. возможности выбора контрагента в модальном окне проверки контрагента: `Или выберите из уже добавленных контрагентов`'
    ) ?>
</p>

<div id="<?php echo $bind_block_id ?>">
    <div class="row">
        <div class="col-12">
            <div class="contractor-bind-error" style="display:none">
                <div class="alert alert-danger">
                    <?php echo Yii::t(
                        'common/widgets/contractor-field',
                        'Сообщение об ошибке в модальном окне подтверждения контрагента: `Произошла ошибка`'
                    ); ?>
                </div>
            </div>
            <label for="approve_contractor_subdivision_code">
                <?php echo $contractor->getAttributeLabel('name'); ?>
            </label>
            <div class="form-group required">
                <?php echo Select2::widget([
                    'name' => 'approve_contractor_id',
                    'language' => Yii::$app->language,
                    'data' => [],
                    'options' => [
                        'placeholder' => Yii::t(
                            'common/widgets/contractor-field',
                            'Подпись пустого значения выпадающего списка для поля "contractor_id" блока "Контрагент": `Выберите организацию ...`'
                        ),
                        'id' => $config['contractorApproveSelectId'] ?? ContractorField::getIdentifier($model, 'approve_contracor_id', $keynum),
                    ],
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
                        'dropdownParent' => "#{$approve_block_id}",
                    ]
                ]); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <?php echo Html::button(
                    Yii::t('common/widgets/contractor-field', 'Подпись кнопки для сохранения формы; модального окна подтверждения контрагента: `Сохранить`'), 
                    [
                        'class' => 'btn btn-primary float-right bind-existing-contractor-btn',
                        'data-found-block-id' => $found_block_id,
                        'data-bind-block-id' => $bind_block_id,
                        'data-from-input-id' => $config['contractorApproveSelectId'] ?? ContractorField::getIdentifier($model, 'approve_contracor_id', $keynum),
                        'data-target-input-id' => $config['selectInputId'],
                        'data-modal-id' => $config['approveModalId'],
                    ]
                ); ?>
            </div>
        </div>
    </div>
</div>

<?php Modal::end(); ?>
