<?php

use common\components\EntranceTestsRenderer\DirectionTrainingRenderer;
use common\components\EntranceTestsRenderer\DisciplineRenderer;
use common\components\EntranceTestsRenderer\ExamFormRenderer;
use common\components\EntranceTestsRenderer\MinScoreRenderer;
use common\components\EntranceTestsRenderer\PriorityRenderer;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\modules\abiturient\models\bachelor\EgeResult;
use common\modules\abiturient\views\bachelor\assets\CompetitiveGroupEntranceTestsAsset;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\form\ActiveForm;
use kartik\grid\GridView;
use yii\bootstrap4\Html;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;











CompetitiveGroupEntranceTestsAsset::register($this);

$disciplineEgeFormUid = Yii::$app->configurationManager->getCode('discipline_ege_form');
$disciplineEgeForm = ArrayHelper::getValue(StoredDisciplineFormReferenceType::findByUID($disciplineEgeFormUid), 'id', '');

$npHasOnlyWithoutEntranceTests = true;

$isExternalForm = true;
if (empty($form)) {
    $isExternalForm = false;
    $form = ActiveForm::begin([
        'method' => 'POST',
        'action' => Url::toRoute(['bachelor/define-discipline-set', 'id' => $id]),
    ]);
}

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
    'options' => ['class' => 'table-responsive disable-vertical_scroll'],
    'dataProvider' => $competitiveGroupEntranceTest,
    'columns' => [
        [
            'attribute' => 'subject',
            'label' => DirectionTrainingRenderer::getLabel(),
            'headerOptions' => ['class' => 'col-3'],
            'format' => 'raw',
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
            'headerOptions' => ['class' => 'col-1'],
            'value' => function ($model, $combinationOfSpecialtyIdAndSubjectRefId) use (&$npHasOnlyWithoutEntranceTests, $form, $newEgeResult) {
                return PriorityRenderer::renderActiveValue(
                    $model,
                    $combinationOfSpecialtyIdAndSubjectRefId,
                    $form,
                    $newEgeResult,
                    $npHasOnlyWithoutEntranceTests
                );
            },
            'contentOptions' => function ($model) {
                return PriorityRenderer::renderOptions($model);
            }
        ],
        [
            'attribute' => 'discipline',
            'label' => DisciplineRenderer::getLabel(),
            'format' => 'raw',
            'value' => function ($model, $combinationOfSpecialtyIdAndSubjectRefId) use ($form, $newEgeResult, $disable) {
                return DisciplineRenderer::renderActiveValue(
                    $model,
                    $combinationOfSpecialtyIdAndSubjectRefId,
                    $form,
                    $newEgeResult,
                    $disable
                );
            },
            'contentOptions' => function ($model) {
                return DisciplineRenderer::renderOptions($model);
            }
        ],
        [
            'attribute' => 'exam_form',
            'label' => ExamFormRenderer::getLabel(),
            'format' => 'raw',
            'value' => function ($model, $combinationOfSpecialtyIdAndSubjectRefId) use ($form, $newEgeResult, $disciplineEgeForm) {
                return ExamFormRenderer::renderActiveValue(
                    $model,
                    $combinationOfSpecialtyIdAndSubjectRefId,
                    $newEgeResult,
                    $disciplineEgeForm
                );
            },
            'contentOptions' => function ($model) {
                return ExamFormRenderer::renderOptions($model);
            }
        ],
        [
            'attribute' => 'min_score',
            'label' => MinScoreRenderer::getLabel(),
            'contentOptions' => function ($model, $combinationOfSpecialtyIdAndSubjectRefId) {
                return MinScoreRenderer::renderOptions($model, $combinationOfSpecialtyIdAndSubjectRefId);
            }
        ],
    ]
]); ?>

<?php if (!$npHasOnlyWithoutEntranceTests && !$isExternalForm && !$disable) : ?>
    <div class="form-group">
        <?= Html::submitButton(
            Yii::t(
                'abiturient/bachelor/ege/competitive-group-entrance-tests',
                'Подпись кнопки сохранения набора ВИ; на стр. ВИ: `Подтвердить набор вступительных испытаний`'
            ) . TooltipWidget::widget([
                'message' => Yii::$app->configurationManager->getText('confirm_entrant_test_set_tooltip'),
                'params' => 'style="margin-left: 4px;"'
            ]),
            ['class' => 'btn btn-primary float-right']
        ) ?>
    </div>
<?php endif; ?>

<?php if (!$isExternalForm) {
    ActiveForm::end();
};
