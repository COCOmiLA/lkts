<?php





use common\assets\PortalTabularFormAsset;
use kartik\builder\TabularForm;
use kartik\form\ActiveForm;
use yii\helpers\Html;

PortalTabularFormAsset::register($this);

if (!isset($canEdit)) {
    $canEdit = true;
}

$globalTextForAjaxTooltip = Yii::$app->configurationManager->getText('global_text_for_ajax_tooltip');
$this->registerJsVar('globalTextForAjaxTooltip', $globalTextForAjaxTooltip);

$script = <<<JS
    window.initTabular($screening);
JS;
$this->registerJs(
    $script,
    yii\web\View::POS_READY
);

$tabularFormParam = [
    'formName' => 'new_table',
    'id' => "_tabular_form_table$screening",
    'dataProvider' => $dataProvider,
    'attributeDefaults' => [
        'type' => TabularForm::INPUT_TEXT,
    ],
    'attributes' => $attributes,

    'actionColumn' => false,
    'serialColumn' => false,
    'staticOnly' => !$canEdit, 

    'gridSettings' => [
        'condensed' => true,
        'panel' => [
            'heading' => false,
            'before' => false,
            'footer' => false,
            'after' => false
        ]
    ]
];

if ($canEdit) {
    $tabularFormParam['gridSettings']['panel']['after'] =
        Html::button(
            '<i class="fa fa-plus"></i> Добавить',
            [
                'value' => 'add',
                'name' => 'table_button_submit',
                'id' => "_tabular_button_add{$screening}",
                'class' => 'btn btn-success kv-batch-create',
            ]
        ) . ' ' .
        Html::button(
            '<i class="fa fa-times"></i> Удалить',
            [
                'value' => 'delete',
                'name' => 'table_button_submit',
                'class' => 'btn btn-danger kv-batch-delete',
                'id' => "_tabular_button_delete{$screening}",
            ]
        ) . ' ' .
        Html::button(
            '<i class="fa fa-floppy-o"></i> Сохранить',
            [
                'value' => 'save',
                'name' => 'table_button_submit',
                'class' => 'btn btn-primary kv-batch-save',
                'id' => "_tabular_button_save{$screening}",
            ]
        );
} else {
    $tabularFormParam['checkboxColumn'] = $canEdit; 
}

$form = ActiveForm::begin([
    'method' => 'post',
    'id' => "_tabular_form$screening",
    'options' => [
        'class' => 'form-vertical kv-form-bs3 tabular_form',
    ],
    'enableAjaxValidation' => true,
]);
$tabularFormParam['form'] = $form;
echo Html::tag( 
    'input',
    '',
    [
        'type' => 'hidden',
        'id' => "hidden_{$screening}",
        'name' => 'ref_UID',
        'value' => $row['table_reference_UID']
    ]
);
foreach ($parameter_page as $kay => $p_p) {
    echo Html::tag( 
        'input',
        '',
        [
            'name' => $kay,
            'value' => $p_p,
            'type' => 'hidden',
            'id' => "{$kay}_{$screening}",
        ]
    );
}
echo TabularForm::widget($tabularFormParam);
ActiveForm::end();
