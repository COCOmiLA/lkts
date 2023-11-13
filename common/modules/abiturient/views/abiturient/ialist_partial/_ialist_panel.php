<?php

use common\models\relation_presenters\comparison\ComparisonHelper;
use common\models\relation_presenters\comparison\interfaces\IComparisonResult;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\IndividualAchievement;
use common\widgets\TooltipWidget\TooltipWidget;
use kartik\grid\GridView;
use yii\bootstrap4\Html;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\View;











$appLanguage = Yii::$app->language;

if (!isset($applicationComparisonWithSent)) {
    $applicationComparisonWithSent = null;
}

$model = new IndividualAchievement();
$model->user_id = $application->user_id;
$model->application_id = $application->id;

[
    'class' => $ia_class,
    'difference' => $ia_difference,
    'comparisonHelper' => $ia_comparison_helper,
] = ComparisonHelper::buildComparisonAttributes(
    $applicationComparisonWithActual,
    $applicationComparisonWithSent,
    'individualAchievements'
);
$iaCellCallback = $ia_comparison_helper ?
    $ia_comparison_helper->makeGridViewContentOptionsCallback() :
    ComparisonHelper::contentOptionsProxyFunc();

?>

<div class="card mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col-sm-9 col-12">
                <h4>
                    <?= Yii::t(
                        'abiturient/bachelor/individual-achievement/block-individual-achievement',
                        'Заголовок в блоке ИД на странице ИД: `Индивидуальные достижения`'
                    ) ?>
                </h4>
            </div>

            <?php $btnSpacer = ''; ?>
            <div class="col-sm-3 col-12 text-right">
                <?php if ($canEdit) : ?>
                    <?= Html::button(
                        Yii::t(
                            'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                            'Подпись кнопки открытия модального окна добавления нового ИД; на стр. ИД: `Добавить`'
                        ) . TooltipWidget::widget([
                            'message' => Yii::$app->configurationManager->getText('add_ia_tooltip'),
                            'params' => 'style="margin-left: 4px;"'
                        ]),
                        [
                            'class' => "btn btn-primary {$btnSpacer}",
                            'data-toggle' => 'modal',
                            'data-target' => '#ia-new',
                        ]
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    if ($ia_with_same_group = $application->getIndividualAchievementsWithSameLimitedGroup()->all()) {
        $text = Yii::$app->configurationManager->getText('several_achievements_with_same_group_chosen', $application->type ?? null);
        if ($text) {
    ?>
            <div class="alert alert-warning">
                <p> <?= $text ?></p>
                <ul>
                    <?php foreach ($ia_with_same_group as $ia) : ?>
                        <li><?= $ia->getFullDescription() ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
    <?php
        }
    }
    ?>

    <div class="card-body panel-body-zero-padding">
        <div class="mobile-friendly">
            <?= GridView::widget([
                'hover' => true,
                'headerContainer' => ['class' => 'thead-light'],
                'tableOptions' => ['class' => 'table-sm mb-0'],
                'striped' => false,
                'summary' => false,
                'condensed' => true,
                'responsive' => true,
                'floatHeader' => true,
                'responsiveWrap' => false,
                'resizableColumns' => false,
                'dataProvider' => $individualAchievementsDataProvider,
                'layout' => '{items}{pager}',
                'floatOverflowContainer' => true,
                'pager' => [
                    'firstPageLabel' => '<<',
                    'prevPageLabel' => '<',
                    'nextPageLabel' => '>',
                    'lastPageLabel' => '>>',
                ],
                'beforeHeader' => [
                    [
                        'columns' => [
                            [
                                'content' => Yii::t(
                                    'abiturient/bachelor/individual-achievement/block-individual-achievement',
                                    'Название группы достижений в таблице в блоке ИД на странице ИД: `Достижение`'
                                ),
                                'options' => [
                                    'colspan' => 1,
                                    'class' => 'text-center'
                                ]
                            ],
                            [
                                'content' => Yii::t(
                                    'abiturient/bachelor/individual-achievement/block-individual-achievement',
                                    'Название группы реквизитов документов в таблице в блоке ИД на странице ИД: `Реквизиты документа`'
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
                'columns' => [
                    [
                        'attribute' => 'achievementTypeName',
                        'contentOptions' => $iaCellCallback(),
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/block-individual-achievement',
                            'Название колонки "achievementTypeName" в таблице в блоке ИД на странице ИД: `Наименование`'
                        ),
                    ],
                    [
                        'attribute' => 'documentTypeDocumentDescription',
                        'contentOptions' => $iaCellCallback(),
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/block-individual-achievement',
                            'Название колонки "documentTypeDocumentDescription" в таблице в блоке ИД на странице ИД: `Тип документа`'
                        ),
                    ],
                    [
                        'attribute' => 'document_series',
                        'contentOptions' => $iaCellCallback(),
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/block-individual-achievement',
                            'Название колонки "document_series" в таблице в блоке ИД на странице ИД: `Серия`'
                        ),
                    ],
                    [
                        'attribute' => 'document_number',
                        'contentOptions' => $iaCellCallback(),
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/block-individual-achievement',
                            'Название колонки "document_number" в таблице в блоке ИД на странице ИД: `Номер`'
                        ),
                    ],
                    [
                        'attribute' => 'document_date',
                        'contentOptions' => $iaCellCallback(),
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/block-individual-achievement',
                            'Название колонки "document_date" в таблице в блоке ИД на странице ИД: `Дата выдачи`'
                        ),
                    ],
                    [
                        'attribute' => 'contractor_id',
                        'value' => function ($model) {
                            return $model->contractor->name ?? '';
                        },
                        'contentOptions' => $iaCellCallback(),
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/block-individual-achievement',
                            'Название колонки "contractor_id" в таблице в блоке ИД на странице ИД: `Кем выдан`'
                        ),
                    ],
                    'documentCheckStatus',
                    [
                        'attribute' => 'id',
                        'label' => Yii::t(
                            'abiturient/bachelor/individual-achievement/block-individual-achievement',
                            'Название колонки "id" в таблице в блоке ИД на странице ИД: `Действия`'
                        ),
                        'format' => 'raw',
                        'value' => function ($model, $key) use ($canEdit) {
                            $links = '';
                            if ($model->canDownload()) {
                                $url = Url::toRoute([
                                    'site/downloadia',
                                    'id' => $model['id']
                                ]);
                                $btnLabel = Yii::t(
                                    'abiturient/bachelor/individual-achievement/block-individual-achievement',
                                    'Подпись кнопки скачивания в таблице в блоке ИД на странице ИД: `Скачать`'
                                );
                                $links .= Html::a(
                                    "<i class='fa fa-save'></i> {$btnLabel}",
                                    $url,
                                    ['class' => 'btn btn-link']
                                );
                            }

                            $icon = '<i class="fa fa-eye"></i>';
                            $btnLabel = Yii::t(
                                'abiturient/bachelor/individual-achievement/block-individual-achievement',
                                'Подпись кнопки редактирования в таблице в блоке ИД на странице ИД: `Редактировать`'
                            );
                            if ($canEdit) {
                                $btnLabel = Yii::t(
                                    'abiturient/bachelor/individual-achievement/block-individual-achievement',
                                    'Подпись кнопки просмотра в таблице в блоке ИД на странице ИД: `Просмотреть`'
                                );
                                $icon = '<i class="fa fa-pencil"></i>';
                            }
                            $links .= Html::button(
                                "{$icon} {$btnLabel}",
                                [
                                    'class' => 'btn btn-link',
                                    'data-toggle' => 'modal',
                                    'data-target' => "#ia-{$key}"
                                ]
                            );

                            if ($canEdit) {
                                if (!$model->read_only) {
                                    $url = Url::toRoute([
                                        'site/deleteia',
                                        'id' => $model['id']
                                    ]);
                                    $btnLabel = Yii::t(
                                        'abiturient/bachelor/individual-achievement/block-individual-achievement',
                                        'Подпись кнопки удаления в таблице в блоке ИД на странице ИД: `Удалить`'
                                    );
                                    $links .= Html::a(
                                        "<i class='fa fa-remove'></i> {$btnLabel}",
                                        $url,
                                        [
                                            'class' => 'btn btn-link',
                                            'data-confirm' => Yii::t(
                                                'abiturient/bachelor/individual-achievement/block-individual-achievement',
                                                'Подтверждение удаления ИД: `Вы уверены, что хотите удалить это индивидуальное достижение?`'
                                            ),
                                        ]
                                    );
                                }
                            }
                            return Html::tag('div', $links, ['class' => 'd-flex flex-column']);
                        }
                    ]
                ]
            ]); ?>
        </div>
    </div>
</div>


<?php if ($canEdit) : ?>
    <?php Modal::begin([
        'size' => Modal::SIZE_LARGE,
        'options' => [
            'id' => 'ia-new',
            'tabindex' => false 
        ],
        'title' => Html::tag(
            'h4',
            Yii::t(
                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                'Заголовок в модальном окне ИД на странице ИД: `Создание индивидуального достижения`'
            )
        )
    ]); ?>

    <?= $this->render(
        '_ialist_form',
        [
            'model' => $model,
            'action' => ['abiturient/addia', 'app_id' => $application->id],
            'buttonName' => Yii::t(
                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                'Подпись кнопки "Добавить"; в модальном окне ИД на странице ИД: `Добавить`'
            ),
            'label' => Yii::t(
                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                'Подпись лейбла "Добавить"; в модальном окне ИД на странице ИД: `Добавить достижение`'
            ),
            'application' => $application
        ]
    ); ?>

    <?php Modal::end(); ?>
<?php endif; ?>

<?php foreach ($individualAchievementsDataProvider->getModels() as $model) : ?>
    <?php Modal::begin([
        'size' => Modal::SIZE_LARGE,
        'options' => [
            'id' => "ia-{$model->id}",
            'tabindex' => false, 
        ],
        'title' => Html::tag(
            'h4',
            Yii::t(
                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                'Заголовок в модальном окне ИД на странице ИД: `Редактирование индивидуального достижения`'
            )
        )
    ]); ?>

    <?= $this->render(
        '_ialist_form',
        [
            'model' => $model,
            'isReadOnly' => !$canEdit,
            'action' => ['abiturient/addia', 'app_id' => $application->id, 'id' => $model->id],
            'key' => $model->id,
            'buttonName' => Yii::t(
                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                'Подпись кнопки "Сохранить"; в модальном окне ИД на странице ИД: `Сохранить`'
            ),
            'label' => Yii::t(
                'abiturient/bachelor/individual-achievement/individual-achievement-modal',
                'Подпись лейбла "Добавить"; в модальном окне ИД на странице ИД: `Добавить достижение`'
            ),
            'application' => $application
        ]
    ); ?>

    <?php Modal::end(); ?>
<?php endforeach;
