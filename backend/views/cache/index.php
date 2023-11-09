<?php

use backend\assets\CacheAsset;
use kartik\grid\GridView;
use yii\bootstrap4\ActiveForm;
use yii\data\ArrayDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;







$this->title = Yii::t('backend', 'Кэш');
$this->params['breadcrumbs'][] = $this->title;

CacheAsset::register($this);

?>

<?= GridView::widget([
    'hover' => true,
    'headerContainer' => ['class' => 'thead-light'],
    'tableOptions' => ['class' => 'table-sm'],
    'striped' => false,
    'summary' => false,
    'dataProvider' => $dataProvider,
    'pager' => [
        'firstPageLabel' => '<<',
        'prevPageLabel' => '<',
        'nextPageLabel' => '>',
        'lastPageLabel' => '>>',
    ],
    'columns' => [
        'name',
        'class',
        [
            'class' => ActionColumn::class,
            'template' => '{flush-cache}',
            'buttons' => [
                'flush-cache' => function ($url, $model) {
                    return Html::a('<i class="fa fa-refresh"></i>', $url, [
                        'title' => Yii::t('backend', 'Сбросить'),
                        'data-confirm' => Yii::t('backend', 'Вы уверены, что хотите сбросить этот кэш?')
                    ]);
                }
            ]
        ],
    ],
]); ?>

<div class="row">
    <div class="col-12">
        <?= Html::a(
            Yii::t('backend', 'Очистить кэш схемы базы данных'),
            Url::to('clear-database-schema-cache'),
            ['class' => 'btn btn-primary']
        ); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-12">
        <?= Html::a(
            Yii::t('backend', 'Очистить подготовленные ресурсы пользовательской части'),
            Url::to('clear-frontend-asset'),
            ['class' => 'btn btn-primary btn-spacer']
        ); ?>
    </div>

    <div class="col-md-6 col-12">
        <?= Html::a(
            Yii::t('backend', 'Очистить подготовленные ресурсы панели администратора'),
            Url::to('clear-backend-asset'),
            ['class' => 'btn btn-primary btn-spacer']
        ); ?>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <h4><?php echo Yii::t('backend', 'Удалить значение по ключу из кэша') ?></h4>
        <?php ActiveForm::begin([
            'action' => Url::to('flush-cache-key'),
            'method' => 'get',
            'layout' => 'inline',
        ]) ?>
        <?php echo Html::dropDownList(
            'id',
            null,
            ArrayHelper::map($dataProvider->allModels, 'name', 'name'),
            ['class' => 'form-control', 'prompt' => Yii::t('backend', 'Выберите кэш')]
        )
        ?>
        <?php echo Html::input('string', 'key', null, ['class' => 'form-control', 'placeholder' => Yii::t('backend', 'Ключ')]) ?>
        <?php echo Html::submitButton(Yii::t('backend', 'Сбросить'), ['class' => 'btn btn-danger']) ?>
        <?php ActiveForm::end() ?>
    </div>

    <div class="col-6">
        <h4>
            <?= Yii::t('backend', 'Invalidate tag') ?>
        </h4>

        <?php ActiveForm::begin([
            'action' => Url::to('flush-cache-tag'),
            'method' => 'get',
            'layout' => 'inline'
        ]) ?>

        <?= Html::dropDownList(
            'id',
            null,
            ArrayHelper::map($dataProvider->allModels, 'name', 'name'),
            ['class' => 'form-control', 'prompt' => Yii::t('backend', 'Select cache')]
        ) ?>

        <?= Html::input('string', 'tag', null, ['class' => 'form-control', 'placeholder' => Yii::t('backend', 'Tag')]) ?>

        <?= Html::submitButton(Yii::t('backend', 'Сбросить'), ['class' => 'btn btn-danger']) ?>

        <?php ActiveForm::end() ?>
    </div>
</div>