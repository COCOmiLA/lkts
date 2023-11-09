<?php

use common\models\relation_presenters\comparison\ComparisonHelper;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\views\bachelor\assets\OlympiadAsset;
use common\services\abiturientController\bachelor\accounting_benefits\OlympiadsService;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\grid\GridView;
use yii\bootstrap4\Modal;
use yii\data\ArrayDataProvider;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;









OlympiadAsset::register($this);

if (empty($action)) {
    $action = [];
}
if (empty($canEdit)) {
    $canEdit = false;
}
$olympiadCellCallback = $olympiads_comparison_helper ? $olympiads_comparison_helper->makeGridViewContentOptionsCallback() : ComparisonHelper::contentOptionsProxyFunc();

?>
<div class="card-header">
    <div class="row d-flex align-items-center">
        <div class="col-sm-9 col-12">
            <h4>
                <?= Yii::t(
                    'abiturient/bachelor/accounting-benefits/block-olympiad',
                    'Заголовок блока БВИ на странице льгот: `Имеется право на поступление без вступительных испытаний`'
                ) ?>
            </h4>
        </div>

        <?php if ($canEdit) : ?>
            <div class="col-sm-3 col-12 text-right">
                <?= Html::button(
                    Yii::t(
                        'abiturient/bachelor/accounting-benefits/modal-olympiad',
                        'Кнопка открытия модального окна БВИ; на странице льгот: `Добавить`'
                    ),
                    [
                        'data-toggle' => 'modal',
                        'class' => 'btn btn-primary',
                        'data-target' => '#create_modal_windows_oly',
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
                'id' => 'olimps_table',
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
            'floatHeader' => false,
            'toolbar' => [
                [
                    'content' => Html::button('<i class="fa fa-save"></i>', [
                        'type' => 'button',
                        'title' => Yii::t(
                            'abiturient/bachelor/accounting-benefits/block-olympiad',
                            'Текст всплывающей подсказки в тулбаре таблицы; блока БВИ на странице льгот: `Добавить`'
                        ),
                        'class' => 'btn btn-success'
                    ]),
                ],
                '{export}',
                '{toggleData}'
            ],
            'beforeHeader' => [
                [
                    'columns' => [
                        [
                            'content' => Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-olympiad',
                                'Название группы олимпиады в таблице блока БВИ на странице льгот: `Олимпиада`'
                            ),
                            'options' => [
                                'colspan' => 4,
                                'class' => 'text-center'
                            ]
                        ],
                        [
                            'content' => Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-olympiad',
                                'Название группы подтверждающих документов в таблице блока БВИ на странице льгот: `Подтверждающий документ`'
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
                    'attribute' => 'olympiadName',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "olympiadName" в таблице блока БВИ на странице льгот: `Наименование олимпиады`'
                    ),

                    'contentOptions' => $olympiadCellCallback(),
                ],
                [
                    'attribute' => 'olympiadYear',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "olympiadYear" в таблице блока БВИ на странице льгот: `Год`'
                    ),

                    'contentOptions' => $olympiadCellCallback(),
                ],
                [
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "olympiadClass" в таблице блока БВИ на странице льгот: `Класс`'
                    ),
                    'contentOptions' => $olympiadCellCallback(),
                    'attribute' => 'olympiadClass',
                ],
                [
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "specialMarkDescription" в таблице блока БВИ на странице льгот: `Особая отметка`'
                    ),
                    'attribute' => 'specialMarkDescription',
                    'contentOptions' => $olympiadCellCallback(),
                ],
                [
                    'attribute' => 'documentTypeDescription',
                    'contentOptions' => $olympiadCellCallback(),
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "documentTypeDescription" в таблице блока БВИ на странице льгот: `Тип документа`'
                    )
                ],
                [
                    'attribute' => 'document_series',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "document_series" в таблице блока БВИ на странице льгот: `Серия`'
                    ),

                    'contentOptions' => $olympiadCellCallback(),
                ],
                [
                    'attribute' => 'document_number',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "document_number" в таблице блока БВИ на странице льгот: `Номер`'
                    ),

                    'contentOptions' => $olympiadCellCallback(),
                ],
                [
                    'attribute' => 'document_date',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "document_date" в таблице блока БВИ на странице льгот: `Дата выдачи`'
                    ),

                    'contentOptions' => $olympiadCellCallback(),
                ],
                [
                    'attribute' => 'contractor_id',
                    'value' => function ($model) {
                        return $model->contractor->name ?? '';
                    },
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "contractor_id" в таблице блока БВИ на странице льгот: `Кем выдано`'
                    ),
                    'contentOptions' => $olympiadCellCallback(),
                ],
                [
                    'attribute' => 'documentCheckStatus',
                    'label' => (new BachelorPreferences())->getAttributeLabel('documentCheckStatus'),
                ],
                [
                    'attribute' => 'id',
                    'label' => Yii::t(
                        'abiturient/bachelor/accounting-benefits/block-olympiad',
                        'Название колонки "id" в таблице блока БВИ на странице льгот: `Действия`'
                    ),
                    'format' => 'raw',
                    'value' => function (BachelorPreferences $model, $key) use ($canEdit, $olympiadsService) {
                        $links = '';
                        if ($olympiadsService->canDownloadOlympiads($model->id)) {
                            $url = Url::toRoute([
                                'site/download-benefits',
                                'id' => $model->id
                            ]);
                            $btnLabel = Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-olympiad',
                                'Подпись кнопки скачивания в таблице блока БВИ на странице льгот: `Скачать`'
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
                        $hasEnlistedBachelorSpecialities = $model->hasEnlistedBachelorSpecialitiesWithOlympiad();
                        $btnLabel = Yii::t(
                            'abiturient/bachelor/accounting-benefits/block-olympiad',
                            'Подпись кнопки просмотра в таблице блока БВИ на странице льгот: `Просмотреть`'
                        );
                        $icon = '<i class="fa fa-eye"></i>';
                        if ($canEdit && !$hasEnlistedBachelorSpecialities) {
                            $btnLabel = Yii::t(
                                'abiturient/bachelor/accounting-benefits/block-olympiad',
                                'Подпись кнопки редактирования в таблице блока БВИ на странице льгот: `Редактировать`'
                            );
                            $icon = '<i class="fa fa-pencil"></i>';
                        }
                        $links .= Html::button(
                            "{$icon} {$btnLabel}",
                            [
                                'class' => 'btn btn-link',
                                'data-toggle' => 'modal',
                                'data-target' => "#edit_modal_windows_oly_{$key}"
                            ]
                        );
                        if ($canEdit && !$hasEnlistedBachelorSpecialities) {
                            if (!$model->read_only) {
                                $url = Url::toRoute([
                                    'site/delete-benefits',
                                    'id' => $model->id
                                ]);
                                $btnLabel = Yii::t(
                                    'abiturient/bachelor/accounting-benefits/block-olympiad',
                                    'Подпись кнопки удаления в таблице блока БВИ на странице льгот: `Удалить`'
                                );
                                $links .= Html::a(
                                    "<i class='fa fa-remove'></i> {$btnLabel}",
                                    $url,
                                    [
                                        'class' => 'btn btn-link',
                                        'data-confirm' => Yii::t(
                                            'abiturient/bachelor/accounting-benefits/block-olympiad',
                                            'Подтверждение удаления олимпиады: `Вы уверены, что хотите удалить эту олимпиаду?`'
                                        ),
                                    ]
                                );
                            }
                        } elseif ($hasEnlistedBachelorSpecialities) {
                            $tooltip = TooltipWidget::widget([
                                'message' => Yii::$app->configurationManager->getText('tooltip_for_olympiad_related_with_bachelor_speciality_marked_as_enlisted'),
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

<?php if ($canEdit) {
    
    $modalId = 'create_modal_windows_oly';
    Modal::begin([
        'title' => Html::tag(
            'h4',
            Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-olympiad',
                'Заголовок модального окна БВИ на странице льгот: `Создать`'
            )
        ),
        'size' => 'modal-lg',
        'options' => ['tabindex' => false],
        'id' => $modalId,
    ]);

    echo $this->render(
        '@common/components/AccountingBenefits/_form_olymp',
        [
            'id' => $id,
            'model' => $model,
            'items' => $items,
            'action' => $action,
            'canEdit' => $canEdit,
            'modalId' => $modalId,
            'itemsDoc' => $itemsDoc,
            'itemsOlymp' => $itemsOlymp,
            'number' => -1,
            'buttonName' => Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-olympiad',
                'Подпись кнопки для сохранения формы; модального окна БВИ на странице льгот: `Добавить`'
            ),
            'application' => $application,
        ]
    );

    Modal::end();
}

$title = Yii::t(
    'abiturient/bachelor/accounting-benefits/modal-olympiad',
    'Заголовок модального окна БВИ на странице льгот: `Просмотреть`'
);
if ($canEdit) {
    $title = Yii::t(
        'abiturient/bachelor/accounting-benefits/modal-olympiad',
        'Заголовок модального окна БВИ на странице льгот: `Редактировать`'
    );
}
foreach ($providers as $key => $provider) {
    
    $modalId = "edit_modal_windows_oly_{$key}";
    Modal::begin([
        'title' => Html::tag('h4', $title),
        'size' => 'modal-lg',
        'options' => ['tabindex' => false],
        'id' => $modalId,
    ]);

    echo $this->render(
        '@common/components/AccountingBenefits/_form_olymp',
        [
            'id' => $id,
            'items' => $items,
            'canEdit' => $canEdit,
            'model' => $provider,
            'modalId' => $modalId,
            'number' => $key,
            'action' => ['site/edit-olympiads'],
            'itemsDoc' => $itemsDoc,
            'buttonName' => Yii::t(
                'abiturient/bachelor/accounting-benefits/modal-olympiad',
                'Подпись кнопки для сохранения формы; модального окна БВИ на странице льгот: `Сохранить`'
            ),
            'itemsOlymp' => $itemsOlymp,
            'application' => $application,
        ]
    );

    Modal::end();
}
