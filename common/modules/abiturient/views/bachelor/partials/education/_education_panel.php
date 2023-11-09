<?php

use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\EducationData;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\grid\ActionColumn;
use kartik\grid\GridView;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\data\ArrayDataProvider;
use yii\web\View;















if (!isset($applicationComparisonWithSent)) {
    $applicationComparisonWithSent = null;
}

[
    'class' => $class,
    'difference' => $differences,
    'comparisonHelper' => $educationComparisonHelper,
] = ComparisonHelper::buildComparisonAttributes(
    $applicationComparisonWithActual,
    $applicationComparisonWithSent,
    'educations'
);
$cellCallback = $educationComparisonHelper ?
    $educationComparisonHelper->makeGridViewContentOptionsCallback() :
    ComparisonHelper::contentOptionsProxyFunc();

$educationProvider = new ArrayDataProvider(['allModels' => $educationDatum]);

$textForOriginalEpguOnEducationData = Yii::$app->configurationManager->getText('text_for_original_epgu_on_education_data');

?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>
            <?= Yii::t(
                'abiturient/bachelor/education-panel/all',
                'Заголовок панели с образованиями; на странице док. об образ.: `Сведения об образовании`'
            ) ?>
            <?= $differences ?: '' ?>
        </h4>

        <?php if ($allowAddNewEducationAfterApprove || $canEdit) : ?>
            <?= Html::button(
                Yii::t(
                    'abiturient/bachelor/education-panel/education-modal',
                    'Подпись кнопки открытия модального окна добавления нового образования; на странице док. об образ.: `Добавить`'
                ),
                [
                    'data-toggle' => 'modal',
                    'class' => 'btn btn-primary float-right',
                    'data-target' => '#create_education_data',
                ]
            ) ?>
        <?php endif; ?>
    </div>

    <div class="card-body mobile-friendly">
        <?php if ($has_pending_contractor) : ?>
            <?php echo $this->render('@common/modules/abiturient/views/sandbox/partial/_pending_contragent_info'); ?>
        <?php endif; ?>

        <?php $editBtn = Yii::t(
            'abiturient/bachelor/education-panel/education-modal',
            'Подпись кнопки открытия модального окна редактирования образования; на странице док. об образ.: `Редактировать`'
        );
        $canUpdate = $canEdit || $allowAddNewFileToEducationAfterApprove || $allowDeleteFileFromEducationAfterApprove;
        if (!$canUpdate) {
            $editBtn = Yii::t(
                'abiturient/bachelor/education/education-modal',
                'Подпись кнопки открытия модального окна просмотра образования; на странице док. об образ.: `Просмотреть`'
            );
        }
        $deleteBtn = Yii::t(
            'abiturient/bachelor/education-panel/education-modal',
            'Подпись кнопки для удаление образования; на странице док. об образ.: `Удалить`'
        );
        $confirmHeader = Yii::t(
            'abiturient/bachelor/education-panel/modal-confirm',
            'Заголовок модального окна подтверждения удаления на странице док. об образ.: `Подтвердите удаление`'
        );
        $confirmToggleModalBtn = Yii::t(
            'abiturient/bachelor/education-panel/modal-confirm',
            'Подпись кнопки открытия модального окна подтверждения удаления; на странице док. об образ.: `Удалить`'
        );
        $confirmDeleteBtn = Yii::t(
            'abiturient/bachelor/education-panel/modal-confirm',
            'Подпись кнопки подтверждения удаления; в модальном окне удаления на странице док. об образ.: `Удалить`'
        );
        $confirmCancelBtn = Yii::t(
            'abiturient/bachelor/education-panel/modal-confirm',
            'Подпись кнопки отмены удаления; в модальном окне удаления на странице док. об образ.: `Отмена`'
        );
        $confirmText = Yii::t(
            'abiturient/bachelor/education-panel/modal-confirm',
            'Текст сообщения; в модальном окне удаления на странице док. об образ.: `Вы уверены, что хотите удалить данный документ об образовании? Данный документ уже привязан к добавленным направлениям подготовок. Удалив этот документ необходимо будет заполнить поле "Документ об образовании" заново для направлений подготовки`'
        );
        $I = 1;
        $columns = [
            [
                'label' => '#',
                'format' => 'raw',
                'value' => function ($model) use (&$I) {
                    return $model->getDocumentCheckStatusIcon($I++);
                },
            ],
            [
                'attribute' => 'contractor_id',
                'value' => function ($model) {
                    return $model->contractor->name ?? '';
                },
                'label' => (new EducationData())->getAttributeLabel('contractor_id'),
                'contentOptions' => $cellCallback(),
            ],
            [
                'attribute' => 'edu_end_year',
                'label' => (new EducationData())->getAttributeLabel('edu_end_year'),
                'contentOptions' => $cellCallback(),
            ],
            [
                'label' => (new EducationData())->getAttributeLabel('documentTypeDescription'),
                'attribute' => 'documentTypeDescription',
                'contentOptions' => $cellCallback('documentTypeDescription'),
            ],
            [
                'attribute' => 'series',
                'label' => Yii::t('abiturient/bachelor/education/education-data', 'Укороченная подпись для поля "series" формы "Док. об обр.": `Серия`'),
                'contentOptions' => $cellCallback(),
            ],
            [
                'attribute' => 'number',
                'label' => Yii::t('abiturient/bachelor/education/education-data', 'Укороченная подпись для поля "number" формы "Док. об обр.": `Номер`'),
                'contentOptions' => $cellCallback(),
            ],
            [
                'attribute' => 'date_given',
                'label' => (new EducationData())->getAttributeLabel('date_given'),
                'contentOptions' => $cellCallback(),
            ],
            [
                'label' => (new EducationData())->getAttributeLabel('educationTypeDescription'),
                'contentOptions' => $cellCallback('educationTypeDescription'),
                'attribute' => 'educationTypeDescription',
            ],
        ];
        if (!$hideProfileFieldForEducation) {
            $columns[] = [
                'label' => (new EducationData())->getAttributeLabel('profileRefDescription'),
                'attribute' => 'profileRefDescription',
            ];
        } ?>
        <?= GridView::widget([
            'hover' => true,
            'headerContainer' => ['class' => 'thead-light'],
            'striped' => false,
            'summary' => false,
            'pager' => [
                'firstPageLabel' => '<<',
                'prevPageLabel' => '<',
                'nextPageLabel' => '>',
                'lastPageLabel' => '>>',
            ],
            'tableOptions' => ['class' => 'table-sm mb-0'],
            'headerRowOptions' => ['class' => 'thead-light'],
            'dataProvider' => $educationProvider,
            'condensed' => true,
            'rowOptions' => function ($model) use ($textForOriginalEpguOnEducationData) {
                

                if (!$model->original_from_epgu) {
                    return [];
                }

                return [
                    'data-placement' => 'top',
                    'data-toggle' => 'tooltip',
                    'title' => $textForOriginalEpguOnEducationData,
                ];
            },
            'columns' => array_merge(
                $columns,
                [[
                    'class' => ActionColumn::class,
                    'template' => '{update} {delete}',
                    'buttons' => [
                        'update' => function ($url, EducationData $model) use (
                            $canEdit,
                            $editBtn,
                            $allowAddNewFileToEducationAfterApprove,
                            $allowDeleteFileFromEducationAfterApprove
                        ) {
                            $hasEnlistedBachelorSpeciality = $model->hasEnlistedBachelorSpecialities();
                            $icon = '<i class="fa fa-eye"></i>';
                            if (
                                $canEdit ||
                                $allowAddNewFileToEducationAfterApprove ||
                                $allowDeleteFileFromEducationAfterApprove
                            ) {
                                $icon = '<i class="fa fa-pencil"></i>';
                            }
                            $result = Html::button(
                                "{$icon} {$editBtn}",
                                [
                                    'class' => 'btn btn-link',
                                    'data-toggle' => 'modal',
                                    'data-target' => "#edit_education_{$model->id}"
                                ]
                            );
                            if (!$canEdit || $hasEnlistedBachelorSpeciality) {
                                if ($hasEnlistedBachelorSpeciality) {
                                    $result .= '<i class="fa fa-check small_verified_status"></i>' .
                                        TooltipWidget::widget(
                                            [
                                                'message' => Yii::$app->configurationManager->getText('tooltip_for_education_related_with_bachelor_speciality_marked_as_enlisted'),
                                                'params' => 'data-container="body"',
                                            ]
                                        );
                                }
                            }

                            return $result;
                        },
                        'delete' => function ($url, EducationData $model) use (
                            $canEdit,
                            $deleteBtn,
                            $application,
                            $confirmText,
                            $confirmHeader,
                            $confirmCancelBtn,
                            $confirmDeleteBtn,
                            $confirmToggleModalBtn
                        ) {
                            if (
                                $canEdit &&
                                !$model->read_only &&
                                !$model->hasEnlistedBachelorSpecialities()
                            ) {
                                if ($model->hasBachelorSpecialities()) {
                                    ob_start();

                                    $footerCancelBtn = Html::button(
                                        $confirmCancelBtn,
                                        [
                                            'data-dismiss' => 'modal',
                                            'class' => 'btn btn-outline-secondary',
                                        ]
                                    );
                                    $footerDeleteBtn = Html::a(
                                        $confirmDeleteBtn,
                                        ['/bachelor/delete-education', 'app_id' => $application->id, 'edu_id' => $model->id],
                                        ['class' => 'btn btn-primary']
                                    );
                                    Modal::begin([
                                        'title' => Html::tag('h4', $confirmHeader),
                                        'toggleButton' => [
                                            'tag' => 'button',
                                            'class' => 'btn btn-link',
                                            'label' => "<i class='fa fa-remove'></i> {$confirmToggleModalBtn}"
                                        ],
                                        'footer' => $footerCancelBtn . $footerDeleteBtn
                                    ]);

                                    echo $confirmText;

                                    Modal::end();
                                    return ob_get_clean();
                                }
                                return Html::a(
                                    "<i class='fa fa-remove'></i> {$deleteBtn}",
                                    ['/bachelor/delete-education', 'app_id' => $application->id, 'edu_id' => $model->id],
                                    [
                                        'class' => 'btn btn-link',
                                        'data-confirm' => Yii::t(
                                            'abiturient/bachelor/education-panel/modal-confirm',
                                            'Подтверждение удаления образования: `Вы уверены, что хотите удалить этот документ об образовании?`'
                                        ),

                                    ]
                                );
                            }
                            return '';
                        },
                    ],
                ]]
            )
        ]); ?>
    </div>
</div>