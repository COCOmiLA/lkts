<?php

use kartik\grid\GridView;
use yii\helpers\Html;

$this->title = 'Заблокированные заявления';
$this->params['breadcrumbs'][] = $this->title;

$loggedId = Yii::$app->user->getId();

?>

<p>
    <?= Html::a('Разблокировать все заявления', ['unblock'], ['class' => 'btn btn-success']) ?>
</p>
<?php try {
    echo GridView::widget([
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm'],
        'striped' => false,
        'summary' => false,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
        'columns' => [
            'id',
            [
                'attribute' => 'fio',
            ],
            [
                'attribute' => 'usermail',
                'contentOptions' => ['style' => 'font-size:14px;'],
            ],
            [
                'header' => 'Направления',
                'attribute' => 'specialitiesString',
                'contentOptions' => ['style' => 'font-size:14px;'],
            ],
            [
                'attribute' => 'sent_at',
                'format' => ['date', 'php:d.m.Y H:i'],
                'contentOptions' => ['style' => 'font-size:14px;'],
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => 'sandboxMessage',
                'contentOptions' => ['style' => 'width: 10%; font-size:14px;'],
            ],
            [
                'header' => 'Изменения',
                'value' => 'historyString',
                'contentOptions' => ['style' => 'width: 20%; font-size:14px;'],
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{unblock}',
                'buttons' => [
                    'unblock' => function ($url, \common\modules\abiturient\models\bachelor\BachelorApplication $model) {
                        $title = '';
                        $messageTime = '';
                        $blockerName = $model->getBlockerName();
                        [$blocked, $time_to_wait] = $model->isApplicationBlocked();
                        $moderating_app = $model->getModeratingApplication();
                        if ($moderating_app && $moderating_app->id != $model->id) {
                            [$blocked, $time_to_wait] = $moderating_app->isApplicationBlocked();
                            $blockerName = $moderating_app->getBlockerName();
                        }
                        if ($blockerName) {
                            
                            
                            if ($blocked && $time_to_wait > 0) {
                                $messageTime = Yii::t(
                                    'sandbox/index/filter-block',
                                    'Текст сообщения, оповещающего о том сколько осталось до окончания блокировки; на стр. поданных заявлений: `Время до разблокировки: {date}`',
                                    ['date' => date('i:s', $time_to_wait)]
                                );
                            }
                            $title = Yii::t(
                                'sandbox/index/filter-block',
                                'Текст всплывающей подсказки на заблокированном заявлении; на стр. поданных заявлений: `Заблокировал: {blockerName} {messageTime}`',
                                [
                                    'blockerName' => $blockerName,
                                    'messageTime' => $messageTime,
                                ]
                            );
                        }


                        return Html::a('Разблокировать', ['unblock', 'id' => $model->id], ['class' => 'btn btn-outline-secondary', 'title' => $title]);
                    },
                ],
                'contentOptions' => ['style' => 'font-size:14px;'],
            ],

        ],
    ]);
} catch (Exception $e) {
    echo 'Не удалось сформировать таблицу';
}
