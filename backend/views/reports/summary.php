<?php

use kartik\grid\GridView;
use yii\grid\SerialColumn;

$this->title = 'Сводная статистика';

?>
<?php if ($summaryDatesProvider->count > 0) : ?>
    <?= GridView::widget([
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm valign-middle text-right'],
        'striped' => false,
        'summary' => false,
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
        'dataProvider' => $summaryDatesProvider,
        'columns' => [
            ['class' => SerialColumn::class],
            [
                'attribute' => 'timestamp',
                'format' => ['date', 'php:d.m.Y'],
            ],
            ['attribute' => 'new_users'],
            ['attribute' => 'new_applications'],
            ['attribute' => 'sended_applications'],
            ['attribute' => 'approved_applications'],
        ],
    ]); ?>
    <p>Всего регистраций: <?= $total['new_users']; ?></p>
    <p>Всего заявлений создано: <?= $total['new_applications']; ?></p>
    <p>Всего заявлений подано: <?= $total['sended_applications']; ?></p>
    <p>Всего заявлений принято: <?= $total['approved_applications']; ?></p>
<?php endif;