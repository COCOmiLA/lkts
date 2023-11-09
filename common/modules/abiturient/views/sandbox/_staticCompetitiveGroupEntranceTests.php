<?php

use common\components\EntranceTestsRenderer\DirectionTrainingRenderer;
use common\components\EntranceTestsRenderer\DisciplineRenderer;
use common\components\EntranceTestsRenderer\ExamFormRenderer;
use common\components\EntranceTestsRenderer\MinScoreRenderer;
use common\components\EntranceTestsRenderer\PriorityRenderer;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\views\bachelor\assets\CompetitiveGroupEntranceTestsAsset;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\View;










$chosenExams = [];
$chosenDisciplines = [];
if (!empty($results)) {
    $chosenExams = ArrayHelper::map($results, 'id', 'cget_exam_form_id');
    $chosenDisciplines = ArrayHelper::map($results, 'id', 'cget_discipline_id');
}

$disciplineEgeFormUid = Yii::$app->configurationManager->getCode('discipline_ege_form');
$disciplineEgeForm = ArrayHelper::getValue(StoredDisciplineFormReferenceType::findByUID($disciplineEgeFormUid), 'id', '');

CompetitiveGroupEntranceTestsAsset::register($this);

$radioIndexList = [];

?>

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
    'options' => ['class' => 'table-responsive'],
    'dataProvider' => $competitiveGroupEntranceTest,
    'columns' => [
        [
            'attribute' => 'subject',
            'label' => DirectionTrainingRenderer::getLabel(),
            'headerOptions' => ['class' => 'col-3'],
            'value' => function ($model) {
                return DirectionTrainingRenderer::renderStaticValue($model);
            },
            'contentOptions' => function ($model) {
                return DirectionTrainingRenderer::renderOptions($model);
            },
        ],
        [
            'attribute' => 'priority',
            'label' => PriorityRenderer::getLabel(),
            'format' => 'raw',
            'value' => function ($model) {
                return PriorityRenderer::renderStaticValue($model);
            },
            'contentOptions' => function ($model) {
                return PriorityRenderer::renderOptions($model);
            }
        ],
        [
            'attribute' => 'discipline',
            'label' => DisciplineRenderer::getLabel(),
            'format' => 'raw',
            'value' => function ($model, $combinationOfSpecialtyIdAndSubjectRefId) {
                return DisciplineRenderer::renderStaticValue($model, $combinationOfSpecialtyIdAndSubjectRefId);
            },
            'contentOptions' => function ($model) {
                return DisciplineRenderer::renderOptions($model);
            }
        ],
        [
            'attribute' => 'exam_form',
            'label' => ExamFormRenderer::getLabel(),
            'format' => 'raw',
            'value' => function ($model, $combinationOfSpecialtyIdAndSubjectRefId) {
                return ExamFormRenderer::renderStaticValue($model, $combinationOfSpecialtyIdAndSubjectRefId);
            },
            'contentOptions' => function ($model) {
                return ExamFormRenderer::renderOptions($model);
            }
        ],
        [
            'attribute' => 'min_score',
            'label' => MinScoreRenderer::getLabel(),
            'value' => function ($model, $combinationOfSpecialtyIdAndSubjectRefId) use ($chosenExams, $chosenDisciplines, $disciplineEgeForm) {
                return MinScoreRenderer::renderStaticValue(
                    $model,
                    $combinationOfSpecialtyIdAndSubjectRefId,
                    $chosenExams,
                    $chosenDisciplines,
                    $disciplineEgeForm
                );
            },
            'contentOptions' => function ($model, $combinationOfSpecialtyIdAndSubjectRefId) {
                return MinScoreRenderer::renderOptions($model, $combinationOfSpecialtyIdAndSubjectRefId);
            }
        ],
    ]
]);