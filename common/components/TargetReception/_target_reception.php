<?php

use common\models\relation_presenters\comparison\ComparisonHelper;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\views\bachelor\assets\TargetReceptionAsset;
use common\services\abiturientController\bachelor\accounting_benefits\TargetReceptionsService;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








TargetReceptionAsset::register($this);

if (empty($action)) {
    $action = [];
}
if (empty($canEdit)) {
    $canEdit = false;
}
$targetsCellCallback = $targets_comparison_helper ? $targets_comparison_helper->makeGridViewContentOptionsCallback() : ComparisonHelper::contentOptionsProxyFunc();

?>

<div class="card-header">
    <div class="row d-flex align-items-center">
        <div class="col-sm-9 col-12">
            <h4>
                <?= Yii::t(
                    'abiturient/bachelor/accounting-benefits/block-target-reception',
                    'Заголовок блока целевые договоры на странице льгот: `По квоте целевого приёма`'
                ) ?>
            </h4>
        </div>

        <?php if ($canEdit) : ?>
            <div class="col-sm-3 col-12 text-right">
                <?= Html::button(
                    Yii::t(
                        'abiturient/bachelor/accounting-benefits/modal-target-reception',
                        'Кнопка открытия модального окна целевых договоров; на странице льгот: `Добавить`'
                    ) . TooltipWidget::widget([
                        'message' => Yii::$app->configurationManager->getText('add_target_tooltip'),
                        'params' => 'style="margin-left: 4px;"'
                    ]),
                    [
                        'class' => 'btn btn-primary',
                        'data-toggle' => 'modal',
                        'data-target' => '#create_modal_window_target'
                    ]
                ) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card-body">
    <div class="mobile-friendly">
        <?= GridView::widget([
            'hover' => true,
            'headerContainer' => ['class' => 'thead-light'],
            'tableOptions' => [
                'class' => 'table-sm mb-0',
                'id' => 'targets_table',
            ],
            'striped' => false,
            'summary' => false,
            'condensed' => true,
            'pager' => [
                'firstPageLabel' => '<<',
                'prevPageLabel' => '<',
                'nextPageLabel' => '>',
                'lastPageLabel' => '>>',
            ],
            'dataProvider' => $dataProvider,
            'layout' => '{items}{pager}',
            'rowOptions' => function ($model, $index, $widget, $grid) {
                return ['data-model-id' => $model->id];
            },
            'floatHeader' => true,
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-target-reception',
                                'Название группы направляющей организации в таблице блока целевые договоры на странице льгот: `Направляющая организация`'
                            ),
                            'options' => [
                                'colspan' => 1,
                                'class' => 'text-center'
                            ]
                        ],
                        [
                            'content' => Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-target-reception',
                                'Название группы подтверждающих документов в таблице блока целевые договоры на странице льгот: `Подтверждающий документ`'
                            ),
                            'options' => [
                                'colspan' => 7,
                                'class' => 'text-center'
                            ]
                        ],
                    ],
                    'options' => ['class' => 'skip-export']
                ]
            ],
            'responsive' => true,
            'responsiveWrap' => false,
            'resizableColumns' => false,
            'floatOverflowContainer' => true,
            'columns' => [
                [
                    'attribute' => 'target_contractor_id',
                    'value' => function ($model) {
                        return $model->targetContractor->name ?? '';
                    },
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-target-reception',
                        'Название колонки "target_contractor_id" в таблице блока целевые договоры на странице льгот: `Наименование организации`'
                    ),
                    'contentOptions' => $targetsCellCallback(),
                ],
                [
                    'attribute' => 'documentTypeDescription',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-target-reception',
                        'Название колонки "documentTypeDescription" в таблице блока целевые договоры на странице льгот: `Тип документа`'
                    )
                ],
                [
                    'attribute' => 'document_series',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-target-reception',
                        'Название колонки "document_series" в таблице блока целевые договоры на странице льгот: `Серия`'
                    ),
                    'contentOptions' => $targetsCellCallback(),
                ],
                [
                    'attribute' => 'document_number',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-target-reception',
                        'Название колонки "document_number" в таблице блока целевые договоры на странице льгот: `Номер`'
                    ),
                    'contentOptions' => $targetsCellCallback(),
                ],
                [
                    'attribute' => 'document_date',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-target-reception',
                        'Название колонки "document_date" в таблице блока целевые договоры на странице льгот: `Дата выдачи`'
                    ),
                    'contentOptions' => $targetsCellCallback(),
                ],
                [
                    'attribute' => 'document_contractor_id',
                    'value' => function ($model) {
                        return $model->documentContractor->name ?? '';
                    },
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-target-reception',
                        'Название колонки "document_contractor_id" в таблице блока целевые договоры на странице льгот: `Кем выдано`'
                    ),
                    'contentOptions' => $targetsCellCallback(),
                ],
                [
                    'attribute' => 'documentCheckStatus',
                    'label' => (new BachelorTargetReception())->getAttributeLabel('documentCheckStatus'),
                ],
                [
                    'attribute' => 'id',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-target-reception',
                        'Название колонки "id" в таблице блока целевые договоры на странице льгот: `Действия`'
                    ),
                    'format' => 'raw',
                    'value' => function (BachelorTargetReception $model, $key) use ($canEdit, $targetReceptionsService) {
                        $links = '';
                        if ($targetReceptionsService->canDownloadTargetReception($model->id)) {
                            $url = Url::toRoute([
                                'site/download-target',
                                'id' => $model->id
                            ]);
                            $btnLabel = Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-target-reception',
                                'Подпись кнопки скачивания в таблице блока целевые договоры на странице льгот: `Скачать`'
                            );

                            $links .= Html::a(
                                "<i class='fa fa-save'></i> {$btnLabel}",
                                $url,
                                [
                                    'class' => 'btn btn-link',
                                    'download' => true
                                ]
                            );
                        }

                        $links .= ' ';
                        $hasEnlistedBachelorSpecialities = $model->hasEnlistedBachelorSpecialities();
                        $icon = '<i class="fa fa-eye"></i>';
                        $btnLabel = Yii::t(
                            'abiturient/bachelor/accounting-benefits/block-target-reception',
                            'Подпись кнопки просмотра в таблице блока целевые договоры на странице льгот: `Просмотреть`'
                        );
                        if ($canEdit && !$hasEnlistedBachelorSpecialities) {
                            $icon = '<i class="fa fa-pencil"></i>';
                            $btnLabel = Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-target-reception',
                                'Подпись кнопки редактирования в таблице блока целевые договоры на странице льгот: `Редактировать`'
                            );
                        }
                        $links .= Html::button(
                            "${icon} {$btnLabel}",
                            [
                                'class' => 'btn btn-link',
                                'data-toggle' => 'modal',
                                'data-target' => "#edit_modal_windows_target_{$key}"
                            ]
                        );
                        if ($canEdit && !$hasEnlistedBachelorSpecialities) {
                            if (!$model->read_only) {
                                $url = Url::toRoute([
                                    'site/delete-target',
                                    'id' => $model->id
                                ]);
                                $btnLabel = Yii::t(
                                    'abiturient/bachelor/accounting-benefits/block-target-reception',
                                    'Подпись кнопки удаления в таблице блока целевые договоры на странице льгот: `Удалить`'
                                );
                                $links .= Html::a(
                                    "<i class='fa fa-remove'></i> {$btnLabel}",
                                    $url,
                                    [
                                        'class' => 'btn btn-link',
                                        'data-confirm' => Yii::t(
                                            'abiturient/bachelor/accounting-benefits/block-target-reception',
                                            'Подтверждение удаления целевого: `Вы уверены, что хотите удалить этот целевой договор?`'
                                        ),
                                    ]
                                );
                            }
                        } elseif ($hasEnlistedBachelorSpecialities) {
                            $tooltip = TooltipWidget::widget([
                                'message' => Yii::$app->configurationManager->getText('tooltip_for_target_reception_related_with_bachelor_speciality_marked_as_enlisted'),
                                'params' => 'style="margin-left: 4px;" data-container="body"'
                            ]);
                            $links .= "<i class='fa fa-check small_verified_status'></i> {$tooltip}";
                        }
                        return Html::tag('div', $links, ['class' => 'd-flex flex-column']);
                    }
                ]
            ]
        ]); ?>
    </div>
</div>

<?php $title = Yii::t(
    'abiturient/bachelor/accounting-benefits/modal-target-reception',
    'Заголовок модального окна целевых договоров на странице льгот: `Просмотреть`'
);
if ($canEdit) {
    $title = Yii::t(
        'abiturient/bachelor/accounting-benefits/modal-target-reception',
        'Заголовок модального окна целевых договоров на странице льгот: `Редактировать`'
    );
}
foreach ($providers as $key => $provider) {
    Modal::begin([
        'title' => Html::tag('h4', $title),
        'size' => 'modal-lg',
        'options' => [
            'tabindex' => false,
        ],
        'id' => "edit_modal_windows_target_{$key}",
    ]);

    echo $this->render(
        '@common/components/TargetReception/_form.php',
        [
            'id' => $id,
            'model' => $provider,
            'items' => $items,
            'number' => $key,
            'canEdit' => $canEdit,
            'action' => ['site/edit-target'],
            'buttonName' => Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-target-reception',
                'Подпись кнопки для сохранения формы; модального окна целевых договоров на странице льгот: `Сохранить`'
            ),
            'application' => $application,
        ]
    );

    Modal::end();
}

if ($canEdit) {
    Modal::begin([
        'title' => Html::tag(
            'h4',
            Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-target-reception',
                'Заголовок модального окна целевых договоров на странице льгот: `Добавить`'
            )
        ),
        'size' => 'modal-lg',
        'options' => [
            'tabindex' => false,
        ],
        'id' => 'create_modal_window_target',
    ]);

    echo $this->render(
        '@common/components/TargetReception/_form.php',
        [
            'id' => $id,
            'model' => $model,
            'items' => $items,
            'action' => $action,
            'canEdit' => $canEdit,
            'application' => $application,
        ]
    );

    Modal::end();
}
