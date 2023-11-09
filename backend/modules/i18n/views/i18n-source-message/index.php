<?php

use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;
use yii\helpers\Html;





$this->title = Yii::t('backend', 'Тексты');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="i18n-source-message-index">
    <p>
        <?php echo Html::a(
            Yii::t(
                'backend',
                'Создание {modelClass}',
                ['modelClass' => 'I18n Source Message',]
            ),
            ['create'],
            ['class' => 'btn btn-success']
        ) ?>
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
            ['class' => SerialColumn::class],
            'id',
            'category',
            'message:ntext',

            ['class' => ActionColumn::class],
        ],
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
    ]); ?>
</div>