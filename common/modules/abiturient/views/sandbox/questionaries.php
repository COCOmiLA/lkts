<?php

$this->title = Yii::$app->name . ' | Проверка заявлений';

use kartik\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\SerialColumn;
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerCssFile('css/manager_style.css', ['depends' => ['frontend\assets\FrontendAsset']]);

?>

<div class="row">
    <div class="col-12">
        <?= $this->render('partial/_notification_and_chat_btns') ?>

        <h2>Поданные заявления</h2>
    </div>
</div>

<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="nav-item <?php if ($type == "moderate") : ?>active<?php endif; ?>">
        <a class="nav-link"
           href="<?php if ($type == "moderate") : ?>#moderate<?php else : ?><?= Url::toRoute("sandbox/index"); ?><?php endif; ?>"
           aria-controls="moderate">
            Требуют проверки
        </a>
    </li>

    <li role="presentation" class="nav-item <?php if ($type == "approved") : ?>active<?php endif; ?>">
        <a class="nav-link"
           href="<?php if ($type == "approved") : ?>#approved<?php else : ?><?= Url::toRoute("sandbox/approved"); ?><?php endif; ?>"
           aria-controls="approved">
            Принятые
        </a>
    </li>

    <li role="presentation" class="nav-item <?php if ($type == "enlisted") : ?>active<?php endif; ?>">
        <a class="nav-link"
           href="<?php if ($type == "enlisted") : ?>#enlisted<?php else : ?><?= Url::toRoute("sandbox/enlisted"); ?><?php endif; ?>"
           aria-controls="enlisted">
            Зачисленные
        </a>
    </li>

    <li role="presentation" class="nav-item <?php if ($type == "declined") : ?>active<?php endif; ?>">
        <a class="nav-link"
           href="<?php if ($type == "declined") : ?>#declined<?php else : ?><?= Url::toRoute("sandbox/declined"); ?><?php endif; ?>"
           aria-controls="declined">
            Отклонённые
        </a>
    </li>

    <li role="presentation" class="nav-item <?php if ($type == "want-delete") : ?>active<?php endif; ?>">
        <a class="nav-link"
           href="<?php if ($type == "want-delete") : ?>#want-delete<?php else : ?><?= Url::toRoute("sandbox/want-delete"); ?><?php endif; ?>"
           aria-controls="deleted">
            Подан отзыв
        </a>
    </li>

    <li role="presentation" class="nav-item <?php if ($type == "deleted") : ?>active<?php endif; ?>">
        <a class="nav-link"
           href="<?php if ($type == "deleted") : ?>#deleted<?php else : ?><?= Url::toRoute("sandbox/deleted"); ?><?php endif; ?>"
           aria-controls="deleted">
            Отозванные
        </a>
    </li>

    <li role="presentation" class="nav-item <?php if ($type == "questionaries") : ?>active<?php endif; ?>">
        <a class="nav-link"
           href="<?php if ($type == "questionaries") : ?>#questionaries<?php else : ?><?= Url::toRoute("sandbox/questionaries"); ?><?php endif; ?>"
           aria-controls="questionaries">
            Анкеты без заявлений
        </a>
    </li>

    <li role="presentation" class="nav-item <?php if ($type == "all") : ?>active<?php endif; ?>">
        <a class="nav-link"
           href="<?php if ($type == "all") : ?>#all<?php else : ?><?= Url::toRoute("sandbox/all"); ?><?php endif; ?>"
           aria-controls="declined">
            Все
        </a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane active" role="tabpanel" id="<?= $type; ?>">
        <?php $emptyText = '<div class="alert alert-info" role="alert">Нет анкет без заявлений</div>'; ?>
        <?= GridView::widget([
            'hover' => true,
            'headerContainer' => ['class' => 'thead-light'],
            'tableOptions' => ['class' => 'table-sm valign-middle'],
            'striped' => false,
            'summary' => false,
            'dataProvider' => $questionaries,
            'filterModel' => $searchModel,
            'emptyText' => $emptyText,
            'pager' => [
                'firstPageLabel' => '<<',
                'prevPageLabel' => '<',
                'nextPageLabel' => '>',
                'lastPageLabel' => '>>',
            ],
            'columns' => [
                ['class' => SerialColumn::class],
                [
                    'attribute' => 'fio',
                ],
                [
                    'attribute' => 'usermail',
                ],
                [
                    'attribute' => 'created_at',
                    'label' => Yii::t('sandbox/questionaries', 'Подпись для колонки с датой создания анкеты: `Дата создания`'),
                    'format' => ['date', 'php:d.m.Y H:i'],
                ],
                [
                    'class' => ActionColumn::class,
                    'template' => '{view-questionary}',
                    'buttons' => [
                        'view-questionary' => function ($url, $model) {
                            return Html::a('<i class="fa fa-eye" aria-hidden="true"></i> Посмотреть', $url, ['class' => 'btn btn-outline-secondary']);
                        }
                    ],

                ],
            ],
        ]); ?>
    </div>
</div>