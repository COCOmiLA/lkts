<?php

use common\models\User;
use kartik\widgets\Alert;
use kartik\widgets\DepDrop;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;



$this->title = Yii::$app->name;

echo Alert::widget([
    'type' => Alert::TYPE_INFO,
    'title' => '<strong>Информация</strong>: ',
    'titleOptions' => ['icon' => 'info-sign'],
    'body' => 'Для отображения данных укажите параметры поиска и нажмите кнопку "Показать"'
]);

$this->registerJs(
<<<JS
/**
 * Данный кусочек скрипта предназначен для замены кнопки "сабмит". При нажатии на кнопку
 * "Показать" из формы выше собирается POST запрос на отправку в "/student/umk",
 * как будто это настоящая кнопка "сабмит". Эот всё ради не перегрузки "DepDrop".
 */
$(document).on('click', 'button#_umk_structure_button', function() {
    var data = $("form#_umk_structure_form").serializeArray();
    $.pjax({
        data : data,
        push : true,
        type : 'GET',
        replace : false,
        timeout : 10000,
        "scrollTo" : false,
        url : '/student/umk',
        container : '#_umk_structure'
    });
    return false;
});

$(document).on('click', 'li a', function() {
    var buffer = $(this);
    $.pjax({
        push : true,
        type : 'GET',
        replace : false,
        timeout : 10000,
        "scrollTo" : false,
        url : buffer[0].href,
        container : '#_umk_structure'
    });
    return false;
});

JS
, yii\web\View::POS_END);

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
    <div class="body-content" id="body-content-id">
        <h3>Учебно-методические материалы</h3>
        <?php if ($role == User::ROLE_STUDENT) : ?>
            <div class="plan-container">
                <div class="plan-header">
                    <?php echo Html::beginForm(Url::toRoute(['student/umk']), 'get', ['id' => '_umk_structure_form']); ?>

                    <div class="row mb-3">
                        <div class="col-12">
                            <?php if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_STUDENT)) { ?>
                                <label for="plan_plan">Учебный план:</label>
                            <?php } elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(User::ROLE_TEACHER)) { ?>
                                <label for="plan_plan">Место работы:</label>
                            <?php }
                            echo Html::dropDownList('plan_plan', $plan_id, ArrayHelper::map($plans, 'id', 'name'), ['class' => 'form-control form-group', 'id' => 'plan_id']); ?>
                        </div>

                        <div class="col-12">
                            <label for="plan_plan">Семестр:</label>
                            <?php
                            echo DepDrop::widget([
                                'name' => 'plan_semester',
                                'value' => $semester_id,
                                'class' => 'form-control form-group',
                                'data' => ArrayHelper::map(is_array($semesters) ? $semesters : [$semesters], 'id', 'name'),
                                'options' => ['id' => 'semester_id', 'placeholder' => 'Выберите семестр'],
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => ['pluginOptions' => [
                                    'allowClear' => false,
                                    'dropdownParent' => '#body-content-id',
                                    'multiple' => false
                                ]],
                                'pluginOptions' => [
                                    'depends' => ['plan_id'],
                                    'placeholder' => 'Выберите семестр',
                                    'url' => Url::to(['/student/semesters']),
                                    'loadingText' => 'Загрузка ...',
                                    'initialize' => true,
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-12">
                            <label for="plan_plan">Дисциплина:</label>
                            <?php
                            echo DepDrop::widget([
                                'name' => 'plan_discipline',
                                'value' => $discipline_id,
                                'class' => 'form-control form-group',
                                'data' => ArrayHelper::map($disciplines, 'id', 'name'),
                                'options' => ['id' => 'discipline_id', 'placeholder' => 'Выберите дисциплину'],
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => ['pluginOptions' => [
                                    'allowClear' => false,
                                    'dropdownParent' => '#body-content-id',
                                    'multiple' => false
                                ]],
                                'pluginOptions' => [
                                    'depends' => ['plan_id', 'semester_id'],
                                    'placeholder' => 'Выберите дисциплину',
                                    'url' => Url::to(['/student/umk/discipline']),
                                    'loadingText' => 'Загрузка ...',
                                    'initialize' => true,
                                ],
                            ]);
                            ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <?php echo Html::button('Показать', ['class' => 'btn btn-primary', 'id' => '_umk_structure_button']); ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <?php if ($role == \common\models\User::ROLE_TEACHER) : ?>
            <div class="plan-container">
                <div class="plan-header">
                    <?php echo Html::beginForm(Url::toRoute(['student/umk']), 'get', []); ?>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="plan_plan">Место работы:</label>
                            <?php echo Html::dropDownList('plan_plan', $plan_id, ArrayHelper::map($plans, 'id', 'name'), ['class' => 'form-control form-group', 'id' => 'plan_id']); ?>
                        </div>

                        <div class="col-12">
                            <label for="plan_plan">Дисциплина:</label>
                            <?php

                            if (!is_array($disciplines))
                                $disciplines = [];

                            echo DepDrop::widget([
                                'name' => 'plan_discipline',
                                'value' => $discipline_id,
                                'class' => 'form-control form-group',
                                'data' => ArrayHelper::map($disciplines, 'id', 'name'),
                                'options' => ['id' => 'discipline_id', 'placeholder' => 'Выберите дисциплину'],
                                'type' => DepDrop::TYPE_SELECT2,
                                'select2Options' => ['pluginOptions' => [
                                    'allowClear' => false,
                                    'dropdownParent' => '#body-content-id',
                                    'multiple' => false
                                ]],
                                'pluginOptions' => [
                                    'depends' => ['plan_id'],
                                    'placeholder' => 'Выберите дисциплину',
                                    'url' => Url::to(['/student/umk/discipline-caf']),
                                    'loadingText' => 'Загрузка ...',
                                    'initialize' => true,
                                ],
                            ]); ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <?php echo Html::submitButton('Показать', ['class' => 'btn btn-primary']); ?>
                        </div>
                    </div>

                    <?php echo Html::endForm(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php Pjax::begin(['options' => ['id' => '_umk_structure']]); ?>
    <div class="body-content">
        <div class="row">
            <div class="col-lg-4">
                <?= Yii::$app->treeParser->treeShow($this, $treeArray); ?>
            </div>

            <div class="col-lg-8">
                <?php Yii::$app->portfolioTable->portfolioTableShow(
                    [
                        'luid'          => $luid,
                        'puid'          => $puid,
                        'files'         => $files,
                        'plan_id'       => $plan_id,
                        'portfolio'     => $portfolio,
                        'discipline_id' => $discipline_id
                    ],
                    'umk'
                ); ?>
            </div>
        </div>
    </div>
    <?php Pjax::end(); ?>
</div>