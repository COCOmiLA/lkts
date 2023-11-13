<?php


use kartik\grid\GridView;
use yii\helpers\Html;





$this->title = Yii::t('backend', 'Приложения OAuth2');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="oauth-index card-body">
    <p>
        <?php echo Html::a(Yii::t('backend', 'Создание {modelClass}', [
            'modelClass' => Yii::t('backend', 'OAuthClients'),
        ]), ['create'], ['class' => 'btn btn-success']) ?>
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
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'client_id',
            'client_secret',
            'redirect_uri',
            'grant_types',
            'scope',
            'user_id',
            ['class' => \yii\grid\ActionColumn::class, 'template' => '{delete}'],
        ],
    ]); ?>

</div>