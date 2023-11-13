<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use common\components\ChecksumManager\models\Checksum;

$this->title = 'Хеш-сумма папки vendor';
$this->params['breadcrumbs'][] = $this->title;

?>

<?php if (Yii::$app->session->hasFlash('checksum-error')) : ?>
    <div class="alert alert-info">
        <?php echo Yii::$app->session->getFlash('checksum-error'); ?>
    </div>
<?php endif; ?>

<?php echo DetailView::widget([
    'model' => $model,
    'attributes' => [
        'checksum',
        'path',
        [
            'label' => Yii::t('common', 'Статус'),
            'value' => function (Checksum $model) {
                return $model->statusDescription;
            }
        ],
        'updated_at:datetime'
    ],
]); ?>

<div class="row mt-2">
    <div class="col-12">
        <?php echo Html::a(
            Yii::t('common', 'Пересчитать'),
            Url::toRoute(['checksum']),
            ['class' => "btn btn-primary", 'data-method' => 'post']
        ); ?>
        <?php echo Html::a(
            Yii::t('common', 'Выгрузить отчет') . ' <i class="fa fa-download"></i>',
            Url::toRoute(['download-checksum-report']),
            ['class' => "btn btn-primary", 'data-method' => 'post']
        ); ?>
    </div>
</div>