<?php





use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;

$dummy_soaps = \common\models\DummySoapResponse::find();
if ($dummy_soaps->exists()) {
    $dataProvider = new ActiveDataProvider([
        'query' => $dummy_soaps,
        'pagination' => [
            'pageSize' => 20,
        ],
    ]);
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
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'method_name',
                'format' => 'text',
                'contentOptions' => ['style' => 'max-width:20vw;']

            ],
            [
                'attribute' => 'methodFormattedResponse',
                'format' => 'html',
                'contentOptions' => ['style' => 'max-width:50vw;']

            ],
            [
                'header' => 'Удалить',
                'class' => ActionColumn::class,
                'template' => '{delete}',
                'buttons' => [
                    'delete' => function ($url, $model, $key) {
                        return \yii\bootstrap4\Html::a('Удалить', [
                            '/log/delete-dummy-soap',
                            'id' => $model->id
                        ], ['class' => 'btn btn-primary']);
                    }
                ]
            ]
        ],
    ]);
} else {
?>
    <div class="alert alert-info dummy_table">
        <span>Нет доступных заглушек для soap</span>
    </div>
<?php
}
