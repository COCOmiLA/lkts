<?php

use common\models\User;
use kartik\widgets\Alert;
use kartik\widgets\AlertBlock;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

echo Alert::widget([
    'type' => Alert::TYPE_INFO,
    'title' => '<strong>Информация</strong>: ',
    'titleOptions' => ['icon' => 'info-sign'],
    'body' => 'Для отображения данных укажите параметры поиска и нажмите кнопку "Показать"'
]);


$this->title = Yii::$app->name;

$this->registerJs("$.fn.modal.Constructor.prototype.enforceFocus = function() {};", View::POS_READY);

$url = Url::to('/student/portfolio/form');
$urlFile = Url::to('/student/portfolio/upload');
$urlLink = Url::to('/student/portfolio/comment');

$this->registerJs(
<<<JS
    $(document).on('click', '#drop-menu > li', function () {
        $('#modal').modal('show')
            .find('#modalContent')
            .load("$url", {
                lcuid: $(this).data('lcuid'),
                puid: "$puid",
                luid: "$luid",
                recordbook_id: "$recordbook_id"
            }, function (responseText, textStatus, req) {
                if (textStatus == "error") {
                    $('#modalContent').html('<div class="alert alert-danger" role="alert">Отсутствуют настройки для описания структуры портфолио. Обратитесь к администратору.</div>');
                }
            });
    });

    $(document).on('click', '.edit-result', function () {
        $('#modalEdit').modal('show')
            .find('#modalEditContent')
            .load("$url", {
                uid: $(this).data('uid'),
                lcuid: $(this).data('lcuid'),
                puid: "$puid",
                luid: "$luid",
                recordbook_id: "$recordbook_id"
            }, function (responseText, textStatus, req) {
                if (textStatus == "error") {
                    $('#modalEditContent').html('<div class="alert alert-danger" role="alert">Отсутствуют настройки для описания структуры портфолио. Обратитесь к администратору.</div>');
                }
            }
            );
    });

    $(document).on('click', '.upload-file', function () {
        $('#modalUpload').modal('show')
            .find('#modalUploadContent')
            .load("$urlFile", {
                uid: $(this).data('uid'),
                puid: "$puid",
                luid: "$luid",
                recordbook_id: "$recordbook_id"
            }, function (responseText, textStatus, req) {
                if (textStatus == "error") {
                    $('#modalUploadContent').html('<div class="alert alert-danger" role="alert">Отсутствуют настройки для описания структуры портфолио. Обратитесь к администратору.</div>');
                }
            });
    });

    $(document).on('click', '.add-comment', function () {
        $('#modalComment').modal('show')
            .find('#modalCommentContent')
            .load("$urlLink", {
                uid: $(this).data('uid'),
                puid: "$puid",
                luid: "$luid",
                recordbook_id: "$recordbook_id"
            }, function (responseText, textStatus, req) {
                if (textStatus == "error") {
                    $('#modalCommentContent').html('<div class="alert alert-danger" role="alert">Отсутствуют настройки для описания структуры портфолио. Обратитесь к администратору.</div>');
                }
            });
    });
JS
    , \yii\web\View::POS_LOAD);


echo AlertBlock::widget([
    'type' => AlertBlock::TYPE_ALERT,
    'useSessionFlash' => true,
    'delay' => false
]);

?>

<div class="body-content">
    <?php 
    $alert = \Yii::$app->session->getFlash('ErrorSoapResponse');
    if (strlen((string)$alert) > 1) {
        echo '<div class="alert alert-danger" role="alert">';
        echo $alert;
        echo '</div>';
    } ?>
</div>

<div class="site-index">
    <div class="body-content">
        <h3>Моё портфолио </h3>
        <div class="plan-header">
            <?= Html::beginForm(Url::toRoute(['student/portfolio']), 'get', []); ?>
            <div class="row">
                <div class="col-12">
                    <?php
                    if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_STUDENT)) {
                    ?> <label for="plan_plan">Учебный план:</label>
                    <?php
                    } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_TEACHER)) {
                    ?>
                        <label for="plan_plan">Место работы:</label>
                    <?php } ?>
                    <?= Html::dropDownList('recordbook_id', $recordbook_id, ArrayHelper::map($recordbooks, 'RecordbookId', 'CurriculumName'), ['class' => 'form-control form-group', 'id' => 'recordbook_id']); ?>
                </div>
                <div class="col-12 mb-3">
                    <?= Html::submitButton('Показать', ['class' => 'btn btn-primary']); ?>
                </div>
            </div>
            <?= Html::endForm(); ?>
        </div>
    </div>

    <div class="body-content">
        <div class="row">
            <div class="col-lg-4">
                <?= Yii::$app->treeParser->treeShow($this, $treeArray); ?>
            </div>

            <div class="col-lg-8">
                <?php Yii::$app->portfolioTable->portfolioTableShow(
                    [
                        'luid' => $luid,
                        'puid' => $puid,
                        'files' => $files,
                        'types' => $types,
                        'marks' => $marks,
                        'comments' => $comments,
                        'portfolio' => $portfolio,
                        'recordbook_id' => $recordbook_id
                    ],
                    'portfolio'
                ); ?>
            </div>
        </div>
    </div>
</div>

<div>
    <?php Modal::begin([
        'headerOptions' => ['id' => 'modalHeader'],
        'id' => 'modal',
        'size' => 'modal-md',
        'title' => 'Добавление портфолио'
    ]); ?>

    <div id="modalContent"></div>

    <?php Modal::end(); ?>

    <?php Modal::begin([
        'id' => 'modalEdit',
        'size' => 'modal-md',
        'title' => 'Отредактировать портфолио',
        'headerOptions' => ['id' => 'modalHeader'],
        'clientOptions' => ['backdrop' => 'static']
    ]); ?>

    <div id="modalEditContent"></div>

    <?php Modal::end(); ?>

    <?php Modal::begin([
        'headerOptions' => ['id' => 'modalHeader'],
        'id' => 'modalUpload',
        'size' => 'modal-md',
        'title' => 'Добавление файла'
    ]); ?>

    <div id="modalUploadContent"></div>

    <?php Modal::end(); ?>

    <?php Modal::begin([
        'headerOptions' => ['id' => 'modalHeader'],
        'id' => 'modalComment',
        'size' => 'modal-md',
        'title' => 'Добавление комментария'
    ]); ?>

    <div id="modalCommentContent"></div>

    <?php Modal::end(); ?>

    <?php Modal::begin([
        'size' => 'modal-lg',
        'id' => 'modalEditTabular',
        'headerOptions' => ['id' => 'modalHeader'],
        'clientOptions' => ['backdrop' => 'static'],
        'title' => Html::tag('h4', 'Редактирование таблицы')
    ]);
    $pathToSvg = FileHelper::normalizePath('/frontend/web/install/img/ajax-loader.svg');
    echo "<div id=\"modalContentImg\"><img src=\"{$pathToSvg}\" class=\"center\" style=\"display: block; margin-left: auto; margin-right: auto; width: 25%;\"/></div>";
    Modal::end(); ?>
</div>