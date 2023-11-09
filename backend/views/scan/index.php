<?php

use backend\models\search\UserSearch;
use common\models\Attachment;
use common\models\AttachmentType;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\grid\ActionColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\ActiveForm;









$this->title = 'Настройка скан-копий';
$additionalParamsForScanTable = [
    'id',
    'name',
    [
        'attribute' => 'related_entity',
        'value' => 'relatedTitle',
    ],
    [
        'attribute' => 'required',
        'value' => 'requiredLabel',
    ],
    [
        'attribute' => 'hidden',
        'value' => 'hiddenLabel',
    ],
    [
        'attribute' => 'allow_delete_file_after_app_approve',
        'value' => 'allowDeleteFileLabel',
    ],
    [
        'attribute' => 'allow_add_new_file_after_app_approve',
        'value' => 'allowAddNewFileLabel',
    ],
];

?>

<?php $scans_msg = Yii::$app->session->get('scans-msg');
if (isset($scans_msg)) : ?>
    <div class="alert alert-warning">
        <p><?= $scans_msg ?></p>
    </div>
<?php endif; ?>

<?php $scans_error = Yii::$app->session->get('scans-error');
if (isset($scans_error)) : ?>
    <div class="alert alert-danger">
        <p><?= $scans_error ?></p>
    </div>
<?php endif; ?>

<?php if (isset($conflictsPKs) && $conflictsPKs) : ?>
    <div class="alert alert-warning">
        <p>
            Обнаружены конфликты при определении типов скан-копий документов. Пожалуйста, укажите корректный тип
            документа на основании существующих скан-копий документов поступающих.
        </p>
    </div>

    <?php $fileErr = Yii::$app->session->get('fileError');
    if (isset($fileErr)) : ?>
        <div class="alert alert-danger">
            <p><?= $fileErr ?></p>
        </div>
    <?php endif; ?>

    <div>
        <ul class="nav nav-tabs" role="tablist">
            <?php
            $active = false;
            foreach ($conflictsPKs as $campaignRefUid => $campaign) : ?>
                <li role="presentation" class="nav-item <?php if ($active == false) {
                                                            echo "active";
                                                            $active = true;
                                                        } ?>">
                    <a class="nav-link" href="#<?= $campaignRefUid ?>" aria-controls="<?= $campaignRefUid ?>" role="tab" data-toggle="tab">
                        <?= $campaign ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content">
            <?php $active = false;
            foreach ($conflictsPKs as $campaignRefUid => $campaign) : ?>
                <div role="tabcard" class="tab-pane <?php if ($active == false) {
                                                        echo "active";
                                                        $active = true;
                                                    } ?>" id="<?= $campaignRefUid ?>">
                    <div>
                        <ul class="nav nav-pills" role="tablist">
                            <?php
                            $conflictsTypes = [];
                            $confs = Attachment::find()
                                ->leftJoin('attachment_type', 'attachment_type.id = attachment.attachment_type_id')
                                ->where(['attachment_type.id' => null])
                                ->all();
                            foreach ($confs as $conf) {
                                if (ArrayHelper::getValue($conf, 'application.type.campaign.referenceType.reference_uid') == $campaignRefUid) {
                                    $conflictsTypes[] = $conf->attachment_type_id;
                                }
                            }
                            $conflictsTypes = array_unique($conflictsTypes);
                            $selectableTypes = [];
                            foreach (AttachmentType::find()
                                ->joinWith('admissionCampaignRef admission_campaign_ref', false)
                                ->where(['from1c' => true])->andWhere(['is_using' => true])->andWhere(['admission_campaign_ref.reference_uid' => $campaignRefUid])->all() as $type) {
                                $selectableTypes[$type->id] = $type->name . " (из 1С)";
                            }
                            foreach (AttachmentType::find()
                                ->joinWith('admissionCampaignRef admission_campaign_ref', false)
                                ->where(['from1c' => true])->andWhere(['is_using' => false])->andWhere(['admission_campaign_ref.reference_uid' => $campaignRefUid])->all() as $type) {
                                $selectableTypes[$type->id] = $type->name . " (из 1С, архивная)";
                            }
                            foreach (AttachmentType::find()->where(['from1c' => [null, false]])->all() as $type) {
                                $selectableTypes[$type->id] = $type->name . " (из портала)";
                            }
                            foreach ($conflictsTypes as $key => $type) : ?>
                                <li role="presentation" class="nav-item <?= $key == 0 ? 'active' : '' ?>">
                                    <a class="nav-link" href="#conflict-<?= $type ?>" aria-controls="conflict-<?= $type ?>" role="tab" data-toggle="tab">Конфликт справочника №<?= $type ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="tab-content">
                            <?php foreach ($conflictsTypes as $key => $type) : ?>
                                <div role="tabcard" class="tab-pane fade <?= $key == 0 ? 'in active' : '' ?>" id="conflict-<?= $type ?>">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="row" style="padding: 20px">
                                                <?php echo Html::beginForm(Url::to(['/scan/solve-conflict', 'id' => $type])); ?>

                                                <div class="col-md-6 col-12 d-flex justify-content-start align-items-end">
                                                    <label for="type" style="margin-bottom: 0; margin-right: 20px">
                                                        Заменить на
                                                        <?php echo Html::dropDownList('type', 'null', $selectableTypes, ['class' => 'form-control']); ?>
                                                    </label>

                                                    <?php echo Html::submitButton('Заменить', ['class' => 'btn btn-success']) ?>
                                                </div>

                                                <?php echo Html::endForm(); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12" style="padding: 20px; max-height: 600px; overflow-y: scroll">
                                            <?php echo GridView::widget([
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
                                                'dataProvider' => new ActiveDataProvider([
                                                    'query' => $conflicts->andWhere(['attachment_type_id' => $type]),
                                                ]),
                                                'columns' => [
                                                    'id',
                                                    'questionary_id',
                                                    'application_id',
                                                    'file',
                                                    [
                                                        'attribute' => 'id',
                                                        'label' => 'Действия',
                                                        'format' => 'raw',
                                                        'value' => function ($model) {
                                                            $links = Html::a('<i class="fa fa-save"></i> Скачать', Url::toRoute(['download', 'id' => $model->id]), ['class' => 'btn btn-link']);
                                                            return Html::tag('div', $links, ['class' => 'd-flex flex-column']);
                                                        }
                                                    ]
                                                ],
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<hr>

<?php $form = ActiveForm::begin(['id' => 'sort-scan-form', 'action' => 'set-scan-sort', 'options' => ['class' => 'form-inline']]);
$items = [
    '0' => 'не сортировать',
    '1' => 'сортировать по алфавиту',
    '2' => 'по ID',
    '3' => 'как в интерфейсе администратора'
];
$params = [
    'prompt' => 'Выберите сортировку по умолчанию...',
    'class' => 'form-control',
]; ?>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            <?= Html::dropDownList('order', $orderValue, $items, $params); ?>

            <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<h3>Скан-копии</h3>

<span>
    <a class='btn btn-success' href="<?php echo Url::toRoute('scan/create'); ?>">
        Добавить скан-копию
    </a>
</span>

<span>
    <button class="btn btn-outline-secondary" id="up-sort" data-toggle="tooltip" title="Переместить выше">
        <i class="fa fa-caret-up"></i>
    </button>
</span>

<span>
    <button class="btn btn-outline-secondary" id="down-sort" data-toggle="tooltip" title="Переместить ниже">
        <i class="fa fa-caret-down"></i>
    </button>
</span>


<?php echo GridView::widget([
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
    'dataProvider' => $scansDataProvider,
    'options' => ['id' => 'scan-copy'],
    'rowOptions' => function ($model) {
        return ['style' => 'cursor: pointer;'];
    },
    'columns' => array_merge(
        $additionalParamsForScanTable,
        [[
            'class' => ActionColumn::class,
            'template' => '{update} {restore} {delete}',
            'contentOptions' => ['class' => 'actions'],
            'buttons' => [
                'restore' => function ($url, $model) {
                    if (!$model->hidden) {
                        return '';
                    }
                    return Html::a('<i class="fa fa-undo"></i>', ['/scan/restore', 'scan_id' => $model->id], [
                        'title' => 'Восстановить',
                        'data-confirm' => 'Вы уверены что хотите вернуть из архива эту скан-копию?',
                        'data-method' => 'post',
                    ]);
                }
            ],
        ]]
    ),
]); ?>
<h3 style="margin-top: 50px">Скан-копии для индивидуальных достижений</h3>
<span>
    <a class='btn btn-success' href="<?php echo Url::toRoute('ia-document-type/create'); ?>">
        Добавить скан-копию И.Д.
    </a>
</span>

<span>
    <button class="btn btn-outline-secondary" id="ia-up-sort" data-toggle="tooltip" title="Переместить выше">
        <i class="fa fa-caret-up"></i>
    </button>
</span>

<span>
    <button class="btn btn-outline-secondary" id="ia-down-sort" data-toggle="tooltip" title="Переместить ниже">
        <i class="fa fa-caret-down"></i>
    </button>
</span>

<?php echo GridView::widget([
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
    'dataProvider' => $iaDocTypes,
    'options' => ['id' => 'ia-scan-copy'],
    'rowOptions' => function ($model) {
        return ['style' => 'cursor: pointer;'];
    },
    'columns' => [
        'id',
        'documentDescription',
        [
            'attribute' => 'scan_required',
            'value' => 'requiredLabel',
        ],
        [
            'label' => 'Приемная кампания',
            'attribute' => 'campaign.name',
            'value' => 'campaign.name',
        ],
        [
            'label' => 'Связанная сущность',
            'attribute' => 'availableDocumentTypeFilterRef.0.reference_name',
            'value' => 'availableDocumentTypeFilterRef.0.reference_name',
        ],
        [
            'class' => ActionColumn::class,
            'template' => '{update} {delete}',
            'controller' => 'ia-document-type',
            'contentOptions' => ['class' => 'actions'],
        ]
    ],
]); ?>
<h3 style="margin-top: 50px">Типы скан-копий из 1С</h3>

<ul class="nav nav-tabs" role="tablist">
    <?php foreach ($admissionCampaigns as $key => $campaign) : ?>
        <li role="presentation" class="nav-item <?= $key == 0 ? 'active' : '' ?>">
            <a class="nav-link" href="#camp-<?= $campaign->referenceType->reference_uid ?>" aria-controls="camp-<?= $campaign->referenceType->reference_uid ?>" role="tab" data-toggle="tab">
                <?= $campaign->name ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<div class="tab-content">
    <?php foreach ($admissionCampaigns as $key => $campaign) : ?>
        <div role="tabcard" class="tab-pane <?= $key == 0 ? 'active' : '' ?>" id="camp-<?= $campaign->referenceType->reference_uid ?>">
            <span>
                <button class="btn btn-outline-secondary" id="1c-up-sort" data-toggle="tooltip" title="Переместить выше">
                    <iconv_mime_encode class="fa fa-caret-up"></iconv_mime_encode>
                </button>
            </span>

            <span>
                <button class="btn btn-outline-secondary" id="1c-down-sort" data-toggle="tooltip" title="Переместить ниже">
                    <i class="fa fa-caret-down"></i>
                </button>
            </span>

            <?php echo GridView::widget([
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
                'dataProvider' => new ActiveDataProvider([
                    'query' => AttachmentType::find()
                        ->where(['from1c' => true])
                        ->joinWith('admissionCampaignRef admission_campaign_ref')
                        ->andWhere(['admission_campaign_ref.reference_uid' => $campaign->referenceType->reference_uid])
                        ->andWhere(['is_using' => true])
                        ->orderBy('custom_order'),
                    'pagination' => false
                ]),
                'layout' => "{items}\n{pager}",
                'rowOptions' => function ($model) {
                    return ['style' => 'cursor: pointer;'];
                },
                'columns' => array_merge(
                    $additionalParamsForScanTable,
                    [[
                        'class' => ActionColumn::class,
                        'contentOptions' => ['class' => 'actions'],
                        'template' => '{update}',
                    ]]
                ),
            ]); ?>
        </div>
    <?php endforeach; ?>
</div>

<?php
$js = <<<JS
    function f(obj, action) {
        var array_row = [];
        obj.each(function () {
            array_row.push($(this).data("key"));
        });
        $.ajax({
            url: action,
            type: "POST",
            data: {
                arrayData: array_row.toString()
            },
            success: function success(res) {
                console.log(res);
            },
            error: function error(res) {
                console.error(res);
            }
        });
    }

    var mainBody = $("body");
    mainBody.on("click", '.tab-content .tab-pane tbody tr td:not(".actions")', function (e) {
        e.preventDefault();
        $(".tab-content .tab-pane").find(".danger").removeClass("danger");
        var thr = $(this).closest("tr");
        thr.addClass("danger");
    });
    mainBody.on("click", "#1c-up-sort", function () {
        var thisRow = $(".tab-content .tab-pane.active").find(".danger");
        var prevRow = thisRow.prev();

        if (prevRow.length) {
            prevRow.before(thisRow);
            f($(".tab-content .tab-pane.active tbody tr"), "sort-scan-copys");
        } else {
            alert("Необходимо выбрать строку для сортировки");
        }
    });
    mainBody.on("click", "#1c-down-sort", function () {
        var thisRow = $(".tab-content .tab-pane.active").find(".danger");
        var nextRow = thisRow.next();

        if (nextRow.length) {
            nextRow.after(thisRow);
            f($(".tab-content .tab-pane.active tbody tr"), "sort-scan-copys");
        } else {
            alert("Необходимо выбрать строку для сортировки");
        }
    });

    $("#scan-copy tbody tr td")
        .not(".actions")
        .on("click", function (e) {
            e.preventDefault();
            $("#scan-copy").find(".danger").removeClass("danger");
            var thr = $(this).closest("tr");
            thr.addClass("danger");
        });

    $("#ia-scan-copy tbody tr td")
        .not(".actions")
        .on("click", function (e) {
            e.preventDefault();
            $("#ia-scan-copy").find(".danger").removeClass("danger");
            var thr = $(this).closest("tr");
            thr.addClass("danger");
        });

    $("#up-sort").on("click", function () {
        var thisRow = $("#scan-copy").find(".danger");
        var prevRow = thisRow.prev();

        if (prevRow.length) {
            prevRow.before(thisRow);
            f($("#scan-copy tbody tr"), "sort-scan-copys");
        } else {
            alert("Необходимо выбрать строку для сортировки");
        }
    });

    $("#down-sort").on("click", function () {
        var thisRow = $("#scan-copy").find(".danger");
        var nextRow = thisRow.next();

        if (nextRow.length) {
            nextRow.after(thisRow);
            f($("#scan-copy tbody tr"), "sort-scan-copys");
        } else {
            alert("Необходимо выбрать строку для сортировки");
        }
    });

    $("#ia-up-sort").on("click", function () {
        var thisRow = $("#ia-scan-copy").find(".danger");
        var prevRow = thisRow.prev();

        if (prevRow.length) {
            prevRow.before(thisRow);
            f($("#ia-scan-copy tbody tr"), "sort-ia-scan-copys");
        } else {
            alert("Необходимо выбрать строку для сортировки");
        }
    });

    $("#ia-down-sort").on("click", function () {
        var thisRow = $("#ia-scan-copy").find(".danger");
        var nextRow = thisRow.next();

        if (nextRow.length) {
            nextRow.after(thisRow);
            f($("#ia-scan-copy tbody tr"), "sort-ia-scan-copys");
        } else {
            alert("Необходимо выбрать строку для сортировки");
        }
    });
JS;
$this->registerJs($js,);
