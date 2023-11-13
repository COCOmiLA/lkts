<?php

use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use kartik\widgets\Select2;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use kartik\form\ActiveForm;

const ATTR_READONLY = 1;
const ATTR_REQUIRED = 2;
const ATTR_HYPERLINK = 3;
const ATTR_VISIBLE = 4;
const ATTR_RECOVERY = 5;
const ATTR_MULTILINE = 6;



$modalTabularName = '';
$tabularRefUID = '';

echo Html::beginTag('div', ['class' => 'row']);
echo Html::beginTag('div', ['class' => 'col-sm-12']);
$form = ActiveForm::begin([
    'action' => '/student/portfolio',
    'enableClientValidation' => true,
    'options' => ['id' => 'add_form_id'],
]);
foreach ($fields_with_label as $i => $field) {
    $attributes = [];
    if (isset($field['attributes']) && !in_array(ATTR_VISIBLE, $field['attributes'])) {
        echo Html::activeHiddenInput($model, $field['name']);
        continue;
    }
    if (isset($field['attributes']) && in_array(ATTR_READONLY, $field['attributes'])) {
        $attributes = ['disabled' => 'disabled'];
        echo Html::activeHiddenInput($model, $field['name']);
    }
    switch ($field['type']) {
        case 'Справочник':
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            echo $form->field($model, $field['name'])->label($field['label'])->widget(Select2::class, [
                'options' => ['placeholder' => 'Выберите значение ...'],
                'language' => 'ru',
                'initValueText' => $field['text'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'minimumInputLength' => 3,
                    'language' => 'ru',
                    'options' => array_merge($attributes, []),
                    'ajax' => [
                        'url' => Url::to([
                            'portfolio/dictionary', 'idx' => $i,
                            'puid' => $model->puid,
                            'luid' => $model->luid,
                            'lcuid' => $model->lcuid,
                            'type' => $field['Ref']
                        ]),
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(city) { return city.text; }'),
                    'templateSelection' => new JsExpression('function (city) { return city.text; }'),
                    'dropdownParent' => '#modalContent',
                ],
            ]);
            echo Html::endTag('div');
            echo Html::endTag('div');
            break;
        case 'Число':
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            echo $form->field($model, $field['name'])->textInput(array_merge(['type' => 'number'], $attributes))->label($field['label']);
            echo Html::endTag('div');
            echo Html::endTag('div');
            break;
        case 'Group':
            $modalTabularName = $field['label'];
            $tabularRefUID = $field['table_reference_UID'];
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            $pathToSvg = FileHelper::normalizePath('/frontend/web/install/img/twotone-table_chart-24px.svg');
            echo Html::a(
                "<img src=\"{$pathToSvg}\" width=\"24 px\" height=\"24px\">",
                '#',
                ['class' => 'btn btn-sm btn-primary float-right EditTabular_button']
            );
            echo $form->field($model, $field['name'])->hiddenInput(['value' => 'hidden value'])->label($field['label']);
            echo Html::endTag('div');
            echo Html::endTag('div');
            break;
        case 'Дата':
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            echo $form->field($model, $field['name'])->label($field['label'])->widget(
                DatePickerMaskedWidget::class,
                [
                    'inline' => false,
                    'language' => 'ru',
                    'template' => '{input}{addon}',
                    'clientOptions' => [
                        'clearBtn' => true,
                        'weekStart' => '1',
                        'autoclose' => true,
                        'todayBtn' => 'linked',
                        'format' => 'dd.mm.yyyy',
                        'calendarWeeks' => 'true',
                        'todayHighlight' => 'true',
                        'options' => $attributes,
                        'orientation' => 'top left',
                        'endDate' => '0d',
                    ],
                    'maskOptions' => [
                        'alias' => 'dd.mm.yyyy'
                    ]
                ]
            );
            echo Html::endTag('div');
            echo Html::endTag('div');
            break;
        case 'Булево':
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            echo $form->field($model, $field['name'])->checkbox(
                array_merge($attributes, []),
                false
            )->label($field['label']);
            echo Html::endTag('div');
            echo Html::endTag('div');
            break;
        default:
            echo Html::beginTag('div', ['class' => 'row']);
            echo Html::beginTag('div', ['class' => 'col-12']);
            if (in_array(ATTR_MULTILINE, $field['attributes'])) {
                echo $form->field($model, $field['name'])
                    ->textarea($attributes)
                    ->label($field['label']);
            } else {
                echo $form->field($model, $field['name'])
                    ->textInput($attributes)
                    ->label($field['label']);
            }
            echo Html::endTag('div');
            echo Html::endTag('div');
    }
}

echo Html::activeHiddenInput($model, 'uid');
echo Html::activeHiddenInput($model, 'luid');
echo Html::activeHiddenInput($model, 'puid');
echo Html::activeHiddenInput($model, 'lcuid');
echo Html::activeHiddenInput($model, 'recordbook_id');

if (empty($model->uid)) : ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <?= Html::submitButton('Добавить', ['class' => 'btn btn-primary float-right']) ?>
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="form-group">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary float-right']) ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php ActiveForm::end();
echo Html::endTag('div');
echo Html::endTag('div');








$this->registerJs(
    <<<JS
$(document).on('click', '.EditTabular_button', function(){
    $('#modalEdit').modal('hide');
    $('#modalEditTabular').modal('show') 
        .find('#modalContent')
        .load('/site/tabular', {
            success: 'Success',
            puid: "$model->puid",
            luid: "$model->luid",
            ref_UID: "$tabularRefUID"
        });
    $('#modalEditTabular').find('h4').html('<h4>$modalTabularName</h4>');
});

$('#modalEditTabular').on('hidden.bs.modal', function (e) {
    $('#modalEdit').modal('show');
});
JS
    , yii\web\View::POS_READY); ?>
