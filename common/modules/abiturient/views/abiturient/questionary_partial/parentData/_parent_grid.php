<?php

use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\settings\ParentDataSetting;
use common\modules\abiturient\models\parentData\ParentData;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;







if (!isset($parents_comparison_helper)) {
    $parents_comparison_helper = null;
}
$isReadonly = false;
$disabled = '';
if (!$canEdit) {
    $disabled = 'disabled';
    $isReadonly = true;
}
$cellCallback = ComparisonHelper::contentOptionsProxyFunc();
if ($parents_comparison_helper) {
    $cellCallback = $parents_comparison_helper->makeGridViewContentOptionsCallback();
}

$btnEdit = Yii::t(
    'abiturient/questionary/parent-grid',
    'Подпись кнопки редактирования записи в таблице родителей на странице анкеты поступающего: `Редактировать`'
);
if ($isReadonly) {
    $btnEdit = Yii::t(
        'abiturient/questionary/parent-grid',
        'Подпись кнопки просмотра записи в таблице родителей на странице анкеты поступающего: `Просмотреть`'
    );
}
$editLoader = Yii::t(
    'abiturient/questionary/parent-grid',
    'Подпись для обрабатываемого запроса редактирования записи в таблице родителей на странице анкеты поступающего: `Загрузка...`'
);
$btnDelete = Yii::t(
    'abiturient/questionary/parent-grid',
    'Подпись кнопки удаления записи в таблице родителей на странице анкеты поступающего: `Удалить`'
);
$deleteLoader = Yii::t(
    'abiturient/questionary/parent-grid',
    'Подпись для обрабатываемого запроса удаления записи в таблице родителей на странице анкеты поступающего: `Удаление...`'
);
$parent_setting = ParentDataSetting::findOne(['name' => 'hide_parent_passport_data_in_list']);
$hide_parent_passport_data = $parent_setting ? $parent_setting->value : false;
?>
<div class="mobile-friendly">
    <?php
    echo GridView::widget([
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm mb-0'],
        'striped' => false,
        'summary' => false,
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
        'dataProvider' => $parents,
        'layout' => '{items}{pager}',
        'condensed' => true,
        'floatHeader' => true,
        'resizableColumns' => false,
        'responsiveWrap' => false,
        'responsive' => true,
        'floatOverflowContainer' => true,
        'columns' => [
            [
                'label' => Yii::t(
                    'abiturient/questionary/parent-grid',
                    'Подпись колонки "type.name" таблицы родителей на странице анкеты поступающего: `Степень родства`'
                ),
                'value' => 'type.name',
                'contentOptions' => $cellCallback('typeName')
            ],
            [
                'label' => Yii::t(
                    'abiturient/questionary/parent-grid',
                    'Подпись колонки "personalData.absFullName" таблицы родителей на странице анкеты поступающего: `ФИО`'
                ),
                'value' => 'personalData.absFullName',
                'contentOptions' => $cellCallback('personalData.absFullName')
            ],
            [
                'label' => Yii::t(
                    'abiturient/questionary/parent-grid',
                    'Подпись колонки "personalData.main_phone" таблицы родителей на странице анкеты поступающего: `Телефон`'
                ),
                'value' => 'personalData.main_phone',
                'contentOptions' => $cellCallback('personalData.preparedMainPhone')
            ],
            [
                'label' => Yii::t(
                    'abiturient/questionary/parent-grid',
                    'Подпись колонки "passportData.series" таблицы родителей на странице анкеты поступающего: `Серия паспорта`'
                ),
                'value' => 'passportData.series',
                'contentOptions' => $cellCallback('passportData.series'),
                'visible' => !$hide_parent_passport_data
            ],
            [
                'label' => Yii::t(
                    'abiturient/questionary/parent-grid',
                    'Подпись колонки "passportData.number" таблицы родителей на странице анкеты поступающего: `Номер паспорта`'
                ),
                'value' => 'passportData.number',
                'contentOptions' => $cellCallback('passportData.number'),
                'visible' => !$hide_parent_passport_data
            ],
            [
                'attribute' => 'id',
                'label' => Yii::t(
                    'abiturient/questionary/parent-grid',
                    'Подпись колонки "Действия" таблицы родителей на странице анкеты поступающего: `Действия`'
                ),
                'format' => 'raw',
                'value' => function ($model) use ($isReadonly, $btnEdit, $btnDelete, $editLoader, $deleteLoader) {
                    $icon = '<i class="fa fa-pencil"></i>';
                    if ($isReadonly) {
                        $icon = '<i class="fa fa-eye"></i>';
                    }
                    $links = Html::button(
                        "{$icon} {$btnEdit}",
                        [
                            'data-id' => $model->id,
                            'data-toggle' => 'modal',
                            'data-loading-text' => $editLoader,
                            'class' => 'btn btn-link btn-edit-parent',
                        ]
                    );
                    if (!$isReadonly) {
                        $links .= Html::a(
                            "<i class='fa fa-remove'></i> {$btnDelete}",
                            '#',
                            [
                                'data-loading-text' => $deleteLoader,
                                'data' => ['parent_data_id' => $model['id']],
                                'class' => 'btn btn-link  parent-data-remove',
                            ]
                        );
                    }
                    return Html::tag('div', $links, ['class' => 'd-flex flex-column']);
                }
            ]
        ]
    ]);
    ?>
</div>