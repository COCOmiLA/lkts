<?php

use yii\helpers\Html;
use yii\widgets\DetailView;




$this->title = "{$model->getLevelName()} #{$model->id}";
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Системный журнал'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<p>
    <?php echo Html::a(
        Yii::t('backend', 'Удалить'),
        ['delete', 'id' => $model->id],
        ['class' => 'btn btn-danger', 'data' => ['method' => 'post']]
    ) ?>
</p>

<?php echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'id',
        'level',
        'category',
        [
            'attribute' => 'log_time',
            'format' => 'datetime',
            'value' => (int)$model->log_time
        ],
        'prefix:ntext',
        [
            'attribute' => 'message',
            'format' => 'raw',
            'value' => Html::tag('pre', Html::encode($model->message), ['style' => 'white-space: pre-wrap'])
        ],
    ],
]);
