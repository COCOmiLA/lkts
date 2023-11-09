<?php








use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>

<?php $form = ActiveForm::begin(['id' => 'search-form']); ?>

<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <?= $form->field($model, 'query')->textInput(['class' => 'form-control input-lg', 'autofocus' => true])->label(Yii::t('podium/view', 'Find words')) ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-4 col-sm-offset-2">
        <?= $form->field($model, 'match')->radioList(['all' => Yii::t('podium/view', 'all words'), 'any' => Yii::t('podium/view', 'any word')], ['unselect' => 'all'])->label(Yii::t('podium/view', 'Match')) ?>
    </div>
    <div class="col-sm-4">
        <?= $form->field($model, 'author')->textInput()->label(Yii::t('podium/view', 'Author')) ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-4 col-sm-offset-2">
        <?= $form->field($model, 'dateFrom')->widget(
            DatePickerMaskedWidget::class,
            [
                'inline' => false,
                'language' => 'ru',
                'template' => '{input}{addon}',
                'clientOptions' => [
                    'clearBtn' => false,
                    'weekStart' => '1',
                    'autoclose' => true,
                    'todayBtn' => 'linked',
                    'format' => 'dd.mm.yyyy',
                    'calendarWeeks' => 'true',
                    'todayHighlight' => 'true',
                    'orientation' => 'top left',
                ],
                'maskOptions' => [
                    'alias' => 'dd.mm.yyyy'
                ]
            ]
        )->label(Yii::t('podium/view', 'Date from'));
        ?>
    </div>
    <div class="col-sm-4">
        <?= $form->field($model, 'dateTo')->widget(
            DatePickerMaskedWidget::class,
            [
                'inline' => false,
                'language' => 'ru',
                'template' => '{input}{addon}',
                'clientOptions' => [
                    'clearBtn' => false,
                    'weekStart' => '1',
                    'autoclose' => true,
                    'todayBtn' => 'linked',
                    'format' => 'dd.mm.yyyy',
                    'calendarWeeks' => 'true',
                    'todayHighlight' => 'true',
                    'orientation' => 'top left',
                ],
                'maskOptions' => [
                    'alias' => 'dd.mm.yyyy'
                ]
            ]
        )->label(Yii::t('podium/view', 'Date to'));
        ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <?= $form->field($model, 'forums')->dropDownList($list, ['multiple' => true, 'encode' => false])->label(Yii::t('podium/view', 'Search in Forums')) ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-4 col-sm-offset-2">
        <?= $form->field($model, 'type')->radioList(['posts' => Yii::t('podium/view', 'posts contents'), 'topics' => Yii::t('podium/view', 'threads titles')], ['unselect' => 'posts'])->label(Yii::t('podium/view', 'Search in')) ?>
    </div>
    <div class="col-sm-4">
        <?= $form->field($model, 'display')->radioList(['posts' => Yii::t('podium/view', 'as posts'), 'topics' => Yii::t('podium/view', 'as threads')], ['unselect' => 'topics'])->label(Yii::t('podium/view', 'Display as')) ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-8 col-sm-offset-2">
        <?= Html::submitButton('<span class="glyphicon glyphicon-search"></span> ' . Yii::t('podium/view', 'Search'), ['class' => 'btn btn-block btn-lg btn-primary', 'name' => 'search-button']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
<br><br>