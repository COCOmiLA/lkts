<?php

use common\models\relation_presenters\comparison\ComparisonHelper;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\views\bachelor\assets\BenefitsAsset;
use common\services\abiturientController\bachelor\accounting_benefits\BenefitsService;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;








BenefitsAsset::register($this);

if (empty($action)) {
    $action = [];
}
if (empty($canEdit)) {
    $canEdit = false;
}
$preferencesCellCallback = $preferences_comparison_helper ? $preferences_comparison_helper->makeGridViewContentOptionsCallback() : ComparisonHelper::contentOptionsProxyFunc();

?>
<div class="card-header">
    <div class="row d-flex align-items-center">
        <div class="col-sm-9 col-12">
            <h4>
                <?= Yii::t(
                    'abiturient/bachelor/accounting-benefits/block-benefits',
                    'Заголовок блока льгот на странице льгот: `Имеются отличительные признаки для поступления`'
                ) ?>
            </h4>
        </div>

        <?php if ($canEdit) : ?>
            <div class="col-sm-3 col-12 text-right">
                <?= Html::button(
                    Yii::t(
                        'abiturient/bachelor/accounting-benefits/modal-benefits',
                        'Кнопка открытия модального окна льгот; на странице льгот: `Добавить`'
                    ) . TooltipWidget::widget([
                        'message' => Yii::$app->configurationManager->getText('add_benefit_tooltip'),
                        'params' => 'style="margin-left: 4px;"'
                    ]),
                    ['class' => 'btn btn-primary', 'data-toggle' => 'modal', 'data-target' => "#create_modal_window"]
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
                'id' => 'preferences_table',
            ],
            'striped' => false,
            'summary' => false,
            'pager' => [
                'firstPageLabel' => '<<',
                'prevPageLabel' => '<',
                'nextPageLabel' => '>',
                'lastPageLabel' => '>>',
            ],
            'dataProvider' => $dataProvider,
            'condensed' => true,
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
                                'abiturient/bachelor/accounting-benefits/block-benefits',
                                'Название группы льгот в таблице блока льгот на странице льгот: `Льгота`'
                            ),
                            'options' => [
                                'colspan' => 2,
                                'class' => 'text-center'
                            ]
                        ],
                        [
                            'content' => Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-benefits',
                                'Название группы подтверждающих документов в таблице блока льгот на странице льгот: `Подтверждающий документ`'
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
            'resizableColumns' => false,
            'responsiveWrap' => false,
            'responsive' => true,
            'floatOverflowContainer' => true,
            'columns' => [
                [
                    'attribute' => 'benefitDescription',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-benefits',
                        'Название колонки "benefitDescription" в таблице блока льгот на странице льгот: `Тип льготы`'
                    ),
                    'contentOptions' => $preferencesCellCallback(),
                ],
                [
                    'attribute' => 'benefitSign',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-benefits',
                        'Название колонки "benefitSign" в таблице блока льгот на странице льгот: `Отличительный признак`'
                    ),
                    'contentOptions' => $preferencesCellCallback(),
                ],
                [
                    'attribute' => 'documentTypeDescription',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-benefits',
                        'Название колонки "documentTypeDescription" в таблице блока льгот на странице льгот: `Тип документа`'
                    ),
                    'contentOptions' => $preferencesCellCallback(),
                ],
                [
                    'attribute' => 'document_series',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-benefits',
                        'Название колонки "document_series" в таблице блока льгот на странице льгот: `Серия`'
                    ),
                    'contentOptions' => $preferencesCellCallback(),
                ],
                [
                    'attribute' => 'document_number',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-benefits',
                        'Название колонки "document_number" в таблице блока льгот на странице льгот: `Номер`'
                    ),
                    'contentOptions' => $preferencesCellCallback(),
                ],
                [
                    'attribute' => 'document_date',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-benefits',
                        'Название колонки "document_date" в таблице блока льгот на странице льгот: `Дата выдачи`'
                    ),
                    'contentOptions' => $preferencesCellCallback(),
                ],
                [
                    'attribute' => 'contractor_id',
                    'value' => function ($model) {
                        return $model->contractor->name ?? '';
                    },
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-benefits',
                        'Название колонки "contractor_id" в таблице блока льгот на странице льгот: `Кем выдано`'
                    ),
                    'contentOptions' => $preferencesCellCallback(),
                ],
                [
                    'attribute' => 'documentCheckStatus',
                    'label' => (new BachelorPreferences())->getAttributeLabel('documentCheckStatus'),
                ],
                [
                    'attribute' => 'id',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-benefits',
                        'Название колонки "id" в таблице блока льгот на странице льгот: `Действия`'
                    ),
                    'format' => 'raw',
                    'value' => function (BachelorPreferences $model, $key) use ($canEdit, $benefitsService) {
                        $links = '';
                        if ($benefitsService->canDownloadBenefits($model->id)) {
                            $url = Url::toRoute([
                                'site/download-benefits',
                                'id' => $model->id
                            ]);
                            $btnLabel = Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-benefits',
                                'Подпись кнопки скачивания в таблице блока льгот на странице льгот: `Скачать`'
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
                        $btnLabel = Yii::t(
                            'abiturient/bachelor/accounting-benefits/block-benefits',
                            'Подпись кнопки просмотра в таблице блока льгот на странице льгот: `Просмотреть`'
                        );
                        $icon = '<i class="fa fa-eye"></i>';
                        if ($canEdit && !$hasEnlistedBachelorSpecialities) {
                            $btnLabel = Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-benefits',
                                'Подпись кнопки редактирования в таблице блока льгот на странице льгот: `Редактировать`'
                            );
                            $icon = '<i class="fa fa-pencil"></i>';
                        }
                        $links .= Html::button(
                            "{$icon} {$btnLabel}",
                            [
                                'class' => 'btn btn-link',
                                'data-toggle' => 'modal',
                                'data-target' => "#edit_modal_windows_{$key}"
                            ]
                        );
                        if ($canEdit && !$hasEnlistedBachelorSpecialities) {
                            if (!$model['from1c'] && !$model->read_only) {
                                $url = Url::toRoute([
                                    'site/delete-benefits',
                                    'id' => $model->id
                                ]);
                                $btnLabel = Yii::t(
                                    'abiturient/bachelor/accounting-benefits/block-benefits',
                                    'Подпись кнопки удаления в таблице блока льгот на странице льгот: `Удалить`'
                                );
                                $links .= Html::a(
                                    "<i class='fa fa-remove'></i> {$btnLabel}",
                                    $url,
                                    [
                                        'class' => 'btn btn-link',
                                        'data-confirm' => Yii::t(
                                            'abiturient/bachelor/accounting-benefits/block-benefits',
                                            'Подтверждение удаления льготы: `Вы уверены, что хотите удалить эту льготу?`'
                                        ),
                                    ]
                                );
                            }
                        } elseif ($hasEnlistedBachelorSpecialities) {
                            $tooltip = TooltipWidget::widget([
                                'message' => Yii::$app->configurationManager->getText('tooltip_for_benefits_related_with_bachelor_speciality_marked_as_enlisted'),
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

<?php
$title = Yii::t(
    'abiturient/bachelor/accounting-benefits/modal-benefits',
    'Заголовок модального окна льгот на странице льгот: `Просмотреть`'
);
if ($canEdit) {
    $title = Yii::t(
        'abiturient/bachelor/accounting-benefits/modal-benefits',
        'Заголовок модального окна льгот на странице льгот: `Редактировать`'
    );
}
foreach ($providers as $key => $provider) {
    $modalId = "edit_modal_windows_{$key}";
    Modal::begin([
        'title' => Html::tag('h4', $title),
        'options' => ['tabindex' => false],
        'size' => 'modal-lg',
        'id' => $modalId,
    ]);

    echo $this->render(
        '@common/components/AccountingBenefits/_form',
        [
            'application' => $application,
            'id' => $id,
            'model' => $provider,
            'items' => $items,
            'canEdit' => $canEdit,
            'number' => $key,
            'modalId' => $modalId,
            'action' => ['site/edit-benefits'],
            'itemsDoc' => $itemsDoc,
            'buttonName' => Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-benefits',
                'Подпись кнопки для сохранения формы; модального окна льгот на странице льгот: `Сохранить`'
            )
        ]
    );

    Modal::end();
}

if ($canEdit) {
    $modalId = 'create_modal_window';
    Modal::begin([
        'title' => Html::tag(
            'h4',
            Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-benefits',
                'Заголовок модального окна льгот на странице льгот: `Создать`'
            )
        ),
        'options' => ['tabindex' => false],
        'size' => 'modal-lg',
        'id' => $modalId,
    ]);

    echo $this->render(
        '@common/components/AccountingBenefits/_form',
        [
            'application' => $application,
            'id' => $id,
            'model' => $model,
            'items' => $items,
            'canEdit' => $canEdit,
            'modalId' => $modalId,
            'action' => $action,
            'itemsDoc' => $itemsDoc
        ]
    );

    Modal::end();
}
