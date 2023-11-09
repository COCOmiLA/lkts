<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;

$globalTextForAjaxTooltip = Yii::$app->configurationManager->getText('global_text_for_ajax_tooltip');

?>

<div class="mx-gutters abitlist-header">
    <h3>
        Количество поступающих и поданных заявлений
    </h3>
</div>

<div class="mx-gutters abitlist-filter">
    <?= Html::beginForm(
        Url::toRoute('admission/totalbudget', true),
        'POST',
        ['id' => 'totalbudgetForm']
    ); ?>
    <div class="form-group row">
        <div class="col-2">
            <?= Html::label(
                'Институт:',
                '',
                ['class' => 'col-form-label']
            ); ?>
        </div>

        <div class="col-4">
            <?= Html::dropDownList(
                'institute',
                '',
                $institutes,
                [
                    'class' => 'form-control',
                    'prompt' => 'Выберите подразделение',
                ]
            ); ?>
        </div>

        <div class="col-2">
            <?= Html::label(
                'Форма обучения:',
                '',
                ['class' => 'col-form-label']
            ); ?>
        </div>

        <div class="col-4">
            <?= Html::dropDownList(
                'learnForm',
                '',
                $learnForms,
                [
                    'class' => 'form-control',
                    'prompt' => 'Выберите форму обучения',
                ]
            ); ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-2">
            <?= Html::label(
                'Квалификация:',
                '',
                ['class' => 'col-form-label']
            ); ?>
        </div>

        <div class="col-4">
            <?= Html::dropDownList(
                'qualification',
                '',
                $qualifications,
                ['class' => 'form-control']
            ); ?>
        </div>

        <div class="col-2">
            <?= Html::label(
                'Финансирование:',
                '',
                ['class' => 'col-form-label']
            ); ?>
        </div>

        <div class="col-4">
            <?= Html::dropDownList(
                'financeForm',
                '',
                $finance_forms,
                ['class' => 'form-control']
            ); ?>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-3">
            <?= Html::label(
                'Направление подготовки:',
                '',
                ['class' => 'col-form-label']
            ); ?>
        </div>

        <div class="col-9">
            <?= Select2::widget([
                'name' => 'spec',
                'data' => $specs,
                'options' => [
                    'multiple' => false,
                    'placeholder' => 'Выберите направление ...',
                ],
                'pluginOptions' => ['allowClear' => true],
            ]); ?>
        </div>
    </div>

    <div class="mx-gutters">
        <?= Html::submitButton(
            'Найти',
            [
                'id' => 'submit_btn',
                'class' => 'btn btn-primary float-right',
            ]
        ); ?>
    </div>
    <?= Html::endForm(); ?>
</div>

<div class="mx-gutters abitlist-container"></div>

<?php $url = Url::toRoute('admission/totalbudget', true); ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#totalbudgetForm").submit(function(e) {
            var btnSubmit = $(this).find("#submit_btn");

            var successFunction = function(data) {
                $(".abitlist-container").html(data);
            };

            var ajaxResult = window.ajaxSender(
                "<?= $url ?>",
                "POST",
                $("#totalbudgetForm").serialize(),
                btnSubmit,
                successFunction,
                null,
                "<?= $globalTextForAjaxTooltip ?>"
            );

            e.preventDefault();
        });
    });
    var s2options_d6851687 = {
        "themeCss": ".select2-container--krajee",
        "sizeCss": "",
        "doReset": true,
        "doToggle": false,
        "doOrder": false
    };
    var select2_869c139f = {
        "allowClear": true,
        "theme": "krajee",
        "width": "100%",
        "placeholder": "Выберите направление ...",
        "language": "ru-RU"
    };

    jQuery(document).ready(function() {
        if (jQuery('#w0').data('select2')) {
            jQuery('#w0').select2('destroy');
        }
        jQuery.when(jQuery('#w0').select2(select2_869c139f)).done(initS2Loading('w0', 's2options_d6851687'));
    });
</script>