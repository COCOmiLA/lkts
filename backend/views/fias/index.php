<?php






use kartik\grid\GridView;

$this->title = "Обновление базы ФИАС";
$this->params['breadcrumbs'][] = $this->title;
\backend\assets\FiasUpdateAsset::register($this);
?>
<div class="fias-loader card-body">
    <div class="fias-update-wrapper" style="display: none;margin-bottom: 15px;">
        <h5>Обновление...</h5>
        <div class="progress-wrapper">
            <div class="progress">
                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                     style="width: 0;">
                    <span class="sr-only">60% Complete</span>
                </div>
            </div>
        </div>
        <div class="alert-wrapper" style="display: none;margin-top: 10px;">
        </div>
    </div>
    <?= GridView::widget([
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm'],
        'striped' => false,
        'summary' => false,
        'id' => 'fias_grid',
        'dataProvider' => $dataProvider,
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
        'rowOptions' => function ($model, $key, $index, $grid) {
            return [
                'data' => ['key' => $model['number']],
            ];
        },
        'columns' => [
            [
                'class' => \yii\grid\CheckboxColumn::class,
                'contentOptions' => ['style' => 'width: 30px'],
            ],
            [
                'attribute' => 'number',
                'label' => 'Номер региона',
            ],
            [
                'attribute' => 'name',
                'label' => 'Название региона',
            ],
        ],
    ]); ?>
    <?php echo \yii\helpers\Html::button('Обновить', ['id' => 'fias_update_btn', 'class' => 'btn btn-primary']) ?>
</div>