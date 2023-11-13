<?php

use backend\models\search\UserSearch;
use common\modules\abiturient\models\bachelor\ApplicationType;
use kartik\grid\EnumColumn;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








$this->title = 'Управление приемными кампаниями';

?>

<p>
    <a class='btn btn-success' href="<?= Url::toRoute('admission/create'); ?>">
        Добавить приемную кампанию на портал
    </a>
</p>

<?= GridView::widget([
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
    'dataProvider' => $applicationTypesDataProvider,
    'filterModel' => $searchModel,
    'rowOptions' => function ($model) {
        if ($model->archive) {
            return ['class' => 'table-warning'];
        }
        if ($model->stageTwoStarted() && !$model->haveStageOne()) {
            return ['class' => 'table-success'];
        }
        return [];
    },
    'columns' => [
        'id',
        'name',
        [
            'attribute' => 'campaign_id',
            'value' => 'campaignName',
        ],
        [
            'class' => EnumColumn::class,
            'attribute' => 'campaignArchive',
            'value' => function ($model) {
                if ($model->campaignArchive) {
                    return 'В архиве';
                } else {
                    return 'Актуальна';
                }
            },
            'format' => 'raw',
            'enum' => [
                0 => 'Актуальна',
                1 => 'В архиве'
            ],
            'filter' => [
                0 => 'Актуальна',
                1 => 'В архиве'
            ],
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{update} {info} {list} {delete} {block} {history-change}',
            'buttons' => [
                'update' => function ($url, $model) {
                    if (!$model->archive) {
                        return Html::a('<i class="fa fa-pencil" aria-hidden="true"></i>', $url, ['class' => '']);
                    } else {
                        return '';
                    }
                },
                'delete' => function ($url, $model) {
                    if (!$model->archive) {
                        return Html::a('<i class="fa fa-trash" aria-hidden="true"></i>', $url, ['title' => 'Удалить', 'aria-label' => 'Удалить', 'data-pjax' => '0', 'data-confirm' => 'Вы уверены, что хотите удалить этот элемент?', 'data-method' => 'post']);
                    } else {
                        return '';
                    }
                },
                'info' => function ($url, $model) {
                    if (!$model->archive) {
                        return Html::a('<i class="fa fa-calendar" aria-hidden="true"></i>', $url, ['class' => '']);
                    } else {
                        return '';
                    }
                },
                'block' => function ($url, $model) {
                    if (!$model->archive) {
                        if (!$model->blocked) {
                            return Html::a('<i class="fa fa-ban" aria-hidden="true"></i> Запретить работу', $url, ['class' => '']);
                        } else {
                            return Html::a('<i class="fa fa-check-circle" aria-hidden="true"></i> Разрешить работу', str_replace('block', 'unblock', $url), ['class' => '']);
                        }
                    } else {
                        return '';
                    }
                },
                'history-change' => function ($url, $model) {
                    

                    if (!$model->archive && $model->hasApplicationTypeHistories()) {
                        return Html::a('<i class="fa fa-history" aria-hidden="true"></i> История изменений', $url, ['title' => 'Просмотреть историю изменений ПК']);
                    } else {
                        return '';
                    }
                },
            ]
        ]
    ],
]); ?>

<p>
    <a class='btn btn-success' href="<?= Url::toRoute('admission/unblockall'); ?>">
        Разрешить работу по всем
    </a>
    <a class='btn btn-danger' href="<?= Url::toRoute('admission/blockall'); ?>">
        Запретить работу по всем
    </a>
</p>