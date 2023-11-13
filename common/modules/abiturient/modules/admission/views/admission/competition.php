<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$script = '
        $("#competitionForm").submit(function(e) {

            var url = "' . Url::toRoute("admission/competition", true) . '";

            $.ajax({
                   type: "POST",
                   url: url,
                   data: $("#competitionForm").serialize(),
                   crossDomain: true,
                   success: function(data)
                   {
                        $(".abitlist-container").html(data);
                        var code = $("#fio").children("option").filter(":selected").val();
                        $("html, body").animate({
                            scrollTop: $("#"+code).offset().top
                        }, 500);
                   }
                 });

                e.preventDefault(); 
        });
        if (jQuery("#spec").data("select2")) { jQuery("#spec").select2("destroy"); }
jQuery.when(jQuery("#spec").select2(select2_af5e313d)).done(initS2Loading("spec","s2options_d6851687"));

if (jQuery("#fio").data("select2")) { jQuery("#fio").select2("destroy"); }
jQuery.when(jQuery("#fio").select2(select2_65abd417)).done(initS2Loading("fio","s2options_d6851687"));
            ';
$this->registerJs($script, View::POS_END);
if (isset($fio)) {
    $sc2 = '
            $(document).ready(function(){
                $("#competitionForm").submit();
            });
                ';
    $this->registerJs($sc2, View::POS_END);
}

$this->title = Yii::$app->name . ' | ' . 'Конкурсный список';
?>

<div class="mx-gutters abitlist-header">
    <h3>Конкурсный список</h3>
</div>
<div class="mx-gutters abitlist-filter">
    <?php echo Html::beginForm(Url::toRoute('admission/competition', true), 'POST', ['id' => 'competitionForm']); ?>
    <div class="form-group row">
        <div class="col-2">
            <?php echo Html::label('Институт:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-4">
            <?php echo Html::dropDownList('institute', $head->institute, $institutes, ['class' => 'form-control']); ?>
        </div>
        <div class="col-2">
            <?php echo Html::label('Форма обучения:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-4">
            <?php echo Html::dropDownList('learnForm', $head->learnform_code, $learnForms, ['class' => 'form-control']); ?>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-2">
            <?php echo Html::label('Квалификация:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-4">
            <?php echo Html::dropDownList('qualification', $head->qualificationCode, $qualifications, ['class' => 'form-control']); ?>
        </div>
        <div class="col-2">
            <?php echo Html::label('Финансирование:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-4">
            <?php echo Html::dropDownList('financeForm', $head->finance_code, $finance_forms, ['class' => 'form-control']); ?>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-3">
            <?php echo Html::label('Направление подготовки:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-9">
            <?php
            echo Select2::widget([
                'name' => 'spec',
                'id' => 'spec',
                'data' => $specs,
                'value' => $head->speciality,
                'options' => [
                    'placeholder' => 'Выберите направление ...',
                    'multiple' => false
                ],
                'pluginOptions' => [
                    'allowClear' => false
                ],
            ]);
            ?>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-3">
            <?php echo Html::label('ФИО:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-9">

            <?php
            echo Select2::widget([
                'name' => 'fio',
                'id' => 'fio',
                'data' => $fios,
                'value' => $fio,
                'options' => [
                    'placeholder' => 'Выберите ФИО ...',
                    'multiple' => false
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
            ?>
        </div>
    </div>
    <div class="mx-gutters">
        <?php if (isset($fio)) : ?>
            <?php echo Html::hiddenInput("cur_fio", $fio); ?>
        <?php endif; ?>
        <?php if (isset($code)) : ?>
            <?php echo Html::hiddenInput("code", $code); ?>
        <?php endif; ?>
        <?php echo Html::submitButton('Найти', ['class' => 'btn btn-primary float-right']); ?>
    </div>
    <?php echo Html::endForm(); ?>
</div>
<div class="mx-gutters abitlist-container">
</div>

<script type="text/javascript">
    /*$(document).ready(function(){
        $("#competitionForm").submit(function(e) {

                var url = "<?php echo Url::toRoute("admission/competition", true); ?>";

            $.ajax({
                   type: "POST",
                   url: url,
                   data: $("#competitionForm").serialize(),
                   crossDomain: true,
                   success: function(data)
                   {
                       $(".abitlist-container").html(data);

                       $('html, body').animate({
                            scrollTop: $('#"<?php echo $fio; ?>"').offset().top
                        }, 500);
                   }
                 });

                e.preventDefault(); 
        });
}); 
var s2options_d6851687 = {"themeCss":".select2-container--krajee","sizeCss":"","doReset":true,"doToggle":false,"doOrder":false};
var select2_af5e313d = {"allowClear":false,"theme":"krajee","width":"100%","placeholder":"Выберите направление ...","language":"ru-RU"};

var select2_65abd417 = {"allowClear":true,"theme":"krajee","width":"100%","placeholder":"Выберите ФИО ...","language":"ru-RU"};
        if ($("#spec").data("select2")) { $("#spec").select2("destroy"); }
$.when($("#spec").select2(select2_af5e313d)).done(initS2Loading("spec","s2options_d6851687"));

if ($("#fio").data("select2")) { $("#fio").select2("destroy"); }
$.when($("#fio").select2(select2_65abd417)).done(initS2Loading("fio","s2options_d6851687"));*/
</script>