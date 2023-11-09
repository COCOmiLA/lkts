<?php

use backend\models\search\SystemLogSearch;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;








$this->title = Yii::t('backend', 'Системный журнал');
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?php echo Html::a(Yii::t('backend', 'Очистить'), '/admin/cleaner/index', ['class' => 'btn btn-danger']) ?>
    <?php echo Html::a('Журнал ошибок', ['index'], ['class' => 'btn btn-primary disabled', 'data-method' => 'index']) ?>
    <?php echo Html::a('Журнал событий', ['info'], ['class' => 'btn btn-primary active', 'data-method' => 'info']) ?>
</p>

<?php echo GridView::widget([
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
    'filterModel' => $searchModel,
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => \yii\grid\SerialColumn::class],
        [
            'attribute' => 'level',
            'value' => function ($model) {
                return \yii\log\Logger::getLevelName($model->level);
            },
            'filter' => [
                \yii\log\Logger::LEVEL_ERROR => 'error',
                \yii\log\Logger::LEVEL_WARNING => 'warning',
                \yii\log\Logger::LEVEL_INFO => 'info',
                \yii\log\Logger::LEVEL_TRACE => 'trace',
                \yii\log\Logger::LEVEL_PROFILE_BEGIN => 'profile begin',
                \yii\log\Logger::LEVEL_PROFILE_END => 'profile end'
            ]
        ],
        'category',
        [
            'attribute' => 'prefix',
            'format' => 'text',
            'value' => function ($model) {
                return \yii\helpers\StringHelper::truncate((string)$model->prefix, 60);
            }
        ],
        [
            'attribute' => 'log_time',
            'format' => 'datetime',
            'value' => function ($model) {
                return (int)$model->log_time;
            }
        ],

        [
            'class' => \yii\grid\ActionColumn::class,
            'template' => '{view}{delete}'
        ]
    ]
]);
