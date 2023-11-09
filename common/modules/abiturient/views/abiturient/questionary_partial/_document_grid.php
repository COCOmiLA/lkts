<?php

use common\models\relation_presenters\comparison\ComparisonHelper;
use common\modules\abiturient\models\PassportData;
use common\modules\abiturient\models\questionary\QuestionarySettings;
use kartik\grid\GridView;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;
use yii\web\View;












if (!isset($isReadonly)) {
    $isReadonly = false;
}
if (!isset($show_texts_for_abit)) {
    $show_texts_for_abit = false;
}

$disabled = '';
if (!$canEdit) {
    $disabled = 'disabled';
}
if (!isset($comparison_helper)) {
    $comparison_helper = null;
}
$cellCallback = $comparison_helper ? $comparison_helper->makeGridViewContentOptionsCallback() : ComparisonHelper::contentOptionsProxyFunc();

$btnEdit = Yii::t(
    'abiturient/questionary/passport-grid',
    'Подпись кнопки редактирования записи в таблице паспортов на странице анкеты поступающего: `Редактировать`'
);
if (!$canEdit) {
    $btnEdit = Yii::t(
        'abiturient/questionary/passport-grid',
        'Подпись кнопки просмотра записи в таблице паспортов на странице анкеты поступающего: `Просмотреть`'
    );
}
$btnDelete = Yii::t(
    'abiturient/questionary/passport-grid',
    'Подпись кнопки удаления записи в таблице паспортов на странице анкеты поступающего: `Удалить`'
);

$allowDeleteFileFromOldPassportAfterApprove = QuestionarySettings::getSettingByName('allow_delete_file_from_passport_after_approve');

?>

<?php if ($show_texts_for_abit && $text = Yii::$app->configurationManager->getText('add_previous_passports_text')) : ?>
    <div class="alert alert-info" role="alert">
        <?= $text; ?>
    </div>
<?php endif; ?>

<div class="mobile-friendly">
    <?php
    echo GridView::widget([
        'hover' => true,
        'headerContainer' => ['class' => 'thead-light'],
        'tableOptions' => ['class' => 'table-sm mb-0'],
        'striped' => false,
        'summary' => false,
        'pager' => [
            'firstPageLabel' => '<<',
            'prevPageLabel' => '<',
            'nextPageLabel' => '>',
            'lastPageLabel' => '>>',
        ],
        'dataProvider' => $passports,
        'layout' => '{items}{pager}',
        'condensed' => true,
        'floatHeader' => true,
        'resizableColumns' => false,
        'responsiveWrap' => false,
        'responsive' => true,
        'floatOverflowContainer' => true,
        'beforeHeader' => [
            [
                'columns' => [[
                    'content' => Yii::t(
                        'abiturient/questionary/passport-grid',
                        'Заголовок таблицы паспортов на странице анкеты поступающего: `Реквизиты документа`'
                    ),
                    'options' => [
                        'colspan' => 8,
                        'class' => 'text-center'
                    ]
                ]],
                'options' => ['class' => 'skip-export']
            ]
        ],
        'columns' => [
            [
                'attribute' => 'documentTypeDescription',
                'label' => Yii::t(
                    'abiturient/questionary/passport-grid',
                    'Подпись колонки "documentTypeDescription" таблицы паспортов на странице анкеты поступающего: `Тип документа`'
                ),
                'contentOptions' => $cellCallback('documentTypeDescription')
            ],
            [
                'attribute' => 'series',
                'contentOptions' => $cellCallback()
            ],
            [
                'attribute' => 'number',
                'contentOptions' => $cellCallback()
            ],
            [
                'attribute' => 'issuedBy',
                'contentOptions' => $cellCallback()
            ],
            [
                'attribute' => 'departmentCode',
                'contentOptions' => $cellCallback()
            ],
            [
                'attribute' => 'issued_date',
                'contentOptions' => $cellCallback()
            ],
            'documentCheckStatus',
            [
                'attribute' => 'id',
                'label' => Yii::t(
                    'abiturient/questionary/passport-grid',
                    'Подпись колонки "Действия" таблицы паспортов на странице анкеты поступающего: `Действия`'
                ),
                'format' => 'raw',
                'value' => function (PassportData $model, $key) use (
                    $btnEdit,
                    $canEdit,
                    $btnDelete,
                    $isReadonly,
                    $allowDeleteFileFromOldPassportAfterApprove
                ) {
                    $icon = '<i class="fa fa-eye"></i>';
                    if ($canEdit) {
                        $icon = '<i class="fa fa-pencil"></i>';
                    }
                    $links = Html::button(
                        "{$icon} {$btnEdit}",
                        [
                            'data-id' => $model->id,
                            'data-toggle' => 'modal',
                            'class' => 'btn btn-link btn-edit-passport',
                            'data-document' => $model->document_type_id,
                        ]
                    );
                    if ($canEdit) {
                        if (
                            !$model->read_only &&
                            !($isReadonly && !$allowDeleteFileFromOldPassportAfterApprove)
                        ) {
                            $links .= Html::a(
                                "<i class='fa fa-remove'></i> {$btnDelete}",
                                '#',
                                [
                                    'class' => 'btn btn-link passport-remove',
                                    'data' => ['passport_id' => $model['id']]
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