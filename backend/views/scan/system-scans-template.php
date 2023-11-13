<?php

use kartik\grid\GridView;
use yii\bootstrap4\Html;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\web\View;








$this->title = Yii::t('backend', 'Настройка системных скан-копий');
$additionalParamsForScanTable = [];


$view = $this;

echo GridView::widget([
    'hover' => true,
    'headerContainer' => ['class' => 'thead-light'],
    'tableOptions' => ['class' => 'table-sm'],
    'striped' => false,
    'summary' => false,
    'pager' => [
        'firstPageLabel' => '<<',
        'prevPageLabel' => '<',
        'nextPageLabel' => '>',
        'lastPageLabel' => '>>',
    ],
    'dataProvider' => $scansDataProvider,
    'options' => ['id' => 'scan-copy'],
    'rowOptions' => function ($model) {
        return ['style' => 'cursor: pointer;'];
    },
    'columns' => [
        'id',
        'name',
        [
            'attribute' => 'related_entity',
            'value' => 'relatedTitle',
        ],
        [
            'attribute' => 'required',
            'value' => 'requiredLabel',
        ],
        [
            'attribute' => 'hidden',
            'value' => 'hiddenLabel',
        ],
        [
            'attribute' => 'allow_delete_file_after_app_approve',
            'value' => 'allowDeleteFileLabel',
        ],
        [
            'attribute' => 'allow_add_new_file_after_app_approve',
            'value' => 'allowAddNewFileLabel',
        ],
        [
            'format' => 'raw',
            'label' => Yii::t('backend', 'Шаблон'),
            'value' => function ($model) use ($view) {
                $attachmentTypeTemplate = $model->attachmentTypeTemplate;
                if (!$attachmentTypeTemplate) {
                    return '-';
                }
                $hasFile = $attachmentTypeTemplate->hasFile();
                if (!$hasFile) {
                    return '-';
                }

                return $view->render(
                    '_modal-system-scans-template',
                    compact([
                        'model',
                        'attachmentTypeTemplate',
                    ])
                );
            },
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{system-scans-template-update}',
            'contentOptions' => ['class' => 'actions'],
            'buttons' => ['system-scans-template-update' => function ($url) {
                return Html::a(
                    Html::tag('i', null, ['class' => 'fa fa-pencil']),
                    $url
                );
            }],
        ]
    ],
]);
