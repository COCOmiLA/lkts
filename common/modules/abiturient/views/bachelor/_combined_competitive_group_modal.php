<?php

use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\web\View;






$cancelBtn = Html::button(
    Yii::t(
        'abiturient/bachelor/application/combined-comp-group-modal',
        'Подпись кнопки отмены; модального окна добавления совмещённой квоты на странице НП: `Отмена`'
    ), [
    'data-dismiss' => 'modal',
    'class' => 'btn btn-outline-secondary',
    'id' => 'combined-group-dismiss'
]);

$btnName = Yii::t(
    'abiturient/bachelor/application/combined-comp-group-modal',
    'Подпись кнопки сохранения; модального окна добавления совмещённой квоты на странице НП: `Добавить`'
);
$applyBtn = Html::button($btnName, [
    'class' => "btn btn-primary",
    'id' => "add-combined-group"
]);

echo Modal::widget([
    'id' => 'combined_cg_modal',
    'title' => Html::tag(
        'h4',
        Yii::t(
            'abiturient/bachelor/application/combined-comp-group-modal',
            'Заголовок модального окна для добавления конкурсной группы совмещённой квоты: `Добавление совмещённой квоты`'
        )
    ),
    'options' => [
        'tabindex' => false,
        'data' => [
            'applicationid' => $bachelorApplication->id,
        ]
    ],
    'footer' => $cancelBtn . $applyBtn,
]);
