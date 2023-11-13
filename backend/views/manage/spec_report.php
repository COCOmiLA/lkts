<?php

use backend\components\ReportsPreprocessor;
use kartik\grid\GridView;
use yii\bootstrap4\Html;
use yii\helpers\Url;

$this->title = 'Отчёт по направлениям подготовки';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-12">
        <?= Html::a(
            'Сохранить в <strong>Excel</strong>',
            Url::toRoute(['/manage/spec-report', 'type' => 'make-report']),
            ['class' => 'btn btn-success float-right']
        ) ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="table-responsive">
            <?= GridView::widget([
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
                'columns' => [
                    [
                        'attribute' => 'speciality_name',
                        'label' => $label['speciality_name'] ?? '-',
                    ],
                    [
                        'attribute' => 'speciality_code',
                        'label' => $label['speciality_code'] ?? '-',
                    ],
                    [
                        'attribute' => 'campaign_code',
                        'label' => $label['campaign_code'] ?? '-',
                    ],
                    [
                        'attribute' => 'from1C',
                        'label' => $label['from1C'] ?? '-',
                        'value' => function ($data) {
                            return ReportsPreprocessor::getHumanFriendlyIsIn1C($data);
                        },
                    ],
                    [
                        'attribute' => 'abit_status',
                        'label' => $label['abit_status'] ?? '-',
                        'value' => function ($data) {
                            return ReportsPreprocessor::getHumanFriendlyApplicationStatus($data);
                        },
                    ],
                    [
                        'attribute' => 'abit_draft_status',
                        'label' => $label['abit_draft_status'] ?? '-',
                        'value' => function ($data) {
                            return ReportsPreprocessor::getHumanFriendlyApplicationDraftStatus($data);
                        },
                    ],
                    [
                        'attribute' => 'applications_count',
                        'label' => $label['applications_count'] ?? '-',
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>