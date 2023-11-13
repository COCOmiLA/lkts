<?php

use kartik\datetime\DateTimePicker;
use yii\bootstrap4\Html;
use yii\web\View;





$appLanguage = Yii::$app->language;

$btnLabelAsc = Yii::t(
    'abiturient/change-history',
    'Подпись кнопки изменения направления сортировки истории; в модальном окне истории изменений: `Сортировать по {direction}`',
    ['direction' => Yii::t(
        'abiturient/change-history',
        'Текст направление сортировки "по возрастанию"; в модальном окне истории изменений: `возрастанию <i class="fa fa-sort-numeric-asc" aria-hidden="true"></i>`'
    )]
);

$btnLabelDesc = Yii::t(
    'abiturient/change-history',
    'Подпись кнопки изменения направления сортировки истории; в модальном окне истории изменений: `Сортировать по {direction}`',
    ['direction' => Yii::t(
        'abiturient/change-history',
        'Текст направление сортировки "по убыванию"; в модальном окне истории изменений: `убыванию <i class="fa fa-sort-numeric-desc" aria-hidden="true"></i>`'
    )]
);

?>

<div class="mb-2">
    <div class="row mb-2">
        <div class="col-12 col-sm-6">
            <label class="control-label">
                <?= Yii::t(
                    'abiturient/change-history',
                    'Заголовок поля для указания начала даты отрисовки истории; в модальном окне истории изменений: `Начиная с`'
                ) ?>
            </label>

            <?= DateTimePicker::widget([
                'id' => 'history-date-start',
                'name' => 'history_date_start',
                'options' => ['placeholder' => Yii::t(
                    'abiturient/change-history',
                    'Текст для пустого значения даты начала; в модальном окне истории изменений: `Начало`'
                )],
                'pluginOptions' => [
                    'autoclose' => true,
                    'language' => $appLanguage,
                ]
            ]); ?>
        </div>

        <div class="col-12 col-sm-6">
            <label class="control-label">
                <?= Yii::t(
                    'abiturient/change-history',
                    'Заголовок поля для указания конца даты отрисовки истории; в модальном окне истории изменений: `Заканчивая`'
                ) ?>
            </label>

            <?= DateTimePicker::widget([
                'id' => 'history-date-end',
                'name' => 'history_date_end',
                'options' => ['placeholder' => Yii::t(
                    'abiturient/change-history',
                    'Текст для пустого значения даты окончания; в модальном окне истории изменений: `Конец`'
                )],
                'pluginOptions' => [
                    'autoclose' => true,
                    'language' => $appLanguage,
                ]
            ]); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-8">
            <?= Html::button(
                $btnLabelAsc,
                [
                    'value' => SORT_ASC,
                    'class' => 'btn btn-info',
                    'id' => 'history-sort-direction',
                    'data-value_asc' => SORT_ASC,
                    'data-label_asc' => $btnLabelAsc,
                    'data-value_desc' => SORT_DESC,
                    'data-label_desc' => $btnLabelDesc,
                ]
            ) ?>
        </div>

        <div class="col-4">
            <?= Html::button(
                Yii::t(
                    'abiturient/change-history',
                    'Подпись кнопки применения фильтров; в модальном окне истории изменений: `Применить`'
                ),
                [
                    'id' => 'update-history',
                    'class' => 'btn btn-primary float-sm-right',
                ]
            ) ?>
        </div>
    </div>
</div>