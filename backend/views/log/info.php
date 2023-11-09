<?php








use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\log\Logger;





$this->title = Yii::t('backend', 'Системный журнал');
$this->params['breadcrumbs'][] = $this->title;

?>

<p>
    <?php echo Html::a(Yii::t('backend', 'Очистить'), false, ['class' => 'btn btn-danger', 'data-method' => 'delete']) ?>
    <?php echo Html::a('Журнал ошибок', ['index'], ['class' => 'btn btn-primary active', 'data-method' => 'index']) ?>
    <?php echo Html::a('Журнал событий', ['info'], ['class' => 'btn btn-primary disabled', 'data-method' => 'info']) ?>
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
        ['class' => SerialColumn::class],
        [
            'attribute' => 'level',
            'value' => function ($model) {
                return Logger::getLevelName($model->level);
            },
            'filter' => [
                Logger::LEVEL_ERROR => 'error',
                Logger::LEVEL_WARNING => 'warning',
                Logger::LEVEL_INFO => 'info',
                Logger::LEVEL_TRACE => 'trace',
                Logger::LEVEL_PROFILE_BEGIN => 'profile begin',
                Logger::LEVEL_PROFILE_END => 'profile end'
            ]
        ],
        'category',
        'prefix',
        [
            'attribute' => 'log_time',
            'format' => 'datetime',
            'value' => function ($model) {
                return (int) $model->log_time;
            }
        ],
        'message',
        [
            'class' => ActionColumn::class,
            'template' => '{view}{delete}',
            'buttons' => [
                'view' => function ($model) {
                    return Html::a('<i class="fa fa-eye"></i>', Url::to(['v', 'id' => explode('=', $model)[1]]), [
                        'title' => Yii::t('backend', 'view'),
                    ]);
                },
                'delete' => function ($model) {
                    return Html::a('<i class="fa fa-trash"></i>', Url::to(['del', 'id' => explode('=', $model)[1]]), [
                        'title' => Yii::t('backend', 'Удалить'),
                    ]);
                }
            ]
        ]
    ]
]);
