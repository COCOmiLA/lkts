<?php

use kartik\grid\GridView;
use yii\helpers\Html;





$this->title = Yii::t('backend', 'I18N переводы');
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="i18n-message-index">
    <p>
        <?php echo Html::a(Yii::t('backend', 'Создание {modelClass}', [
            'modelClass' => 'I18n Message',
        ]), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php echo GridView::widget([
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm'],
        'striped' => false,
        'summary' => false,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            [
                'attribute' => 'language',
                'filter' => $languages
            ],
            [
                'attribute' => 'category',
                'filter' => $categories
            ],
            'sourceMessage',
            'translation:ntext',
            ['class' => \yii\grid\ActionColumn::class, 'template' => '{update} {delete}'],
        ],
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
    ]); ?>
</div>