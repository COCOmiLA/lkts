<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$script = '
        $("#specialityForm").submit(function(e) {

            var url = "' . Url::toRoute("admission/speciality", true) . '";

            $.ajax({
                   type: "POST",
                   url: url,
                   data: $("#specialityForm").serialize(),
                   crossDomain: true,
                   success: function(data)
                   {
                       $(".abitlist-container").html(data);
                   }
                 });

                e.preventDefault(); 
        });
            ';
$this->registerJs($script, View::POS_END);
?>
<div class="mx-gutters abitlist-header">
    <h3>Список лиц, подавших документы</h3>
</div>
<div class="mx-gutters abitlist-filter">
    <?php echo Html::beginForm(Url::toRoute('admission/speciality', true), 'POST', ['id' => 'specialityForm']); ?>
    <div class="form-group row">
        <div class="col-2">
            <?php echo Html::label('Квалификация:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-4">
            <?php echo Html::dropDownList('qualification', '', $qualifications, ['class' => 'form-control']); ?>
        </div>
        <div class="col-2">
            <?php echo Html::label('Код специальности:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-4">
            <?php
            echo Select2::widget([
                'name' => 'code',
                'id' => 'code',
                'data' => $codes,
                'options' => [
                    'placeholder' => 'Выберите код специальности ...',
                    'multiple' => false
                ],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]);
            ?>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-2">
            <?php echo Html::label('ФИО:', '', ['class' => 'col-form-label']); ?>
        </div>
        <div class="col-10">
            <?php
            echo Select2::widget([
                'name' => 'fio',
                'id' => 'fio',
                'data' => $fios,
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
        <?php echo Html::submitButton('Найти', ['class' => 'btn btn-primary float-right']); ?>
    </div>
    <?php echo Html::endForm(); ?>
</div>
<div class="mx-gutters abitlist-container">
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $("#specialityForm").submit(function(e) {

            var url = "<?php echo Url::toRoute("admission/speciality", true); ?>";

            $.ajax({
                type: "POST",
                url: url,
                data: $("#specialityForm").serialize(),
                crossDomain: true,
                success: function(data) {
                    $(".abitlist-container").html(data);
                }
            });

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
    var select2_7ebe7326 = {
        "allowClear": true,
        "theme": "krajee",
        "width": "100%",
        "placeholder": "Выберите код специальности ...",
        "language": "ru-RU"
    };

    var select2_65abd417 = {
        "allowClear": true,
        "theme": "krajee",
        "width": "100%",
        "placeholder": "Выберите ФИО ...",
        "language": "ru-RU"
    };

    jQuery(document).ready(function() {
        if (jQuery('#code').data('select2')) {
            jQuery('#code').select2('destroy');
        }
        jQuery.when(jQuery('#code').select2(select2_7ebe7326)).done(initS2Loading('code', 's2options_d6851687'));

        if (jQuery('#fio').data('select2')) {
            jQuery('#fio').select2('destroy');
        }
        jQuery.when(jQuery('#fio').select2(select2_65abd417)).done(initS2Loading('fio', 's2options_d6851687'));

    });
</script>