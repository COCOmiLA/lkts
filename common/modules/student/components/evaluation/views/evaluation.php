<?php

use kartik\widgets\Alert;
use kartik\widgets\AlertBlock;
use kartik\widgets\DepDrop;
use yii\bootstrap4\Modal;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;



$this->title = Yii::$app->name;

$urlFile = Url::to('/student/evaluation/upload');
$urlLink = Url::to('/student/evaluation/comment');
$urlMark = Url::to('/student/evaluation/mark');

echo Alert::widget([
    'type' => Alert::TYPE_INFO,
    'title' => '<strong>Информация</strong>: ',
    'titleOptions' => ['icon' => 'info-sign'],
    'body' => 'Для отображения данных укажите параметры поиска и нажмите кнопку "Показать"'
]);

$this->registerJs(
<<<JS
$(document).on('click', '.upload-file', function(){
    $('#modalUpload').modal('show') 
        .find('#modalContent')
        .load("$urlFile", {
            uid: $(this).data('uid'), 
            puid: "$puid",
            luid: "$luid",
            studentId: "$studentId",
            planId: "$circullumId",
            cafId: "$caf_id"
        });
});

$(document).on('click', '.add-comment', function(){
    $('#modalComment').modal('show') 
        .find('#modalContent')
        .load("$urlLink", {
            uid: $(this).data('uid'), 
            puid: "$puid",
            luid: "$luid",
            studentId: "$studentId",
            planId: "$circullumId",
            cafId: "$caf_id"
        });
});

$(document).on('click', '.add-mark', function(){
    $('#modalMark').modal('show') 
        .find('#modalContent')
        .load("$urlMark", {
            uid: $(this).data('uid'), 
            puid: "$puid",
            luid: "$luid",
            studentId: "$studentId",
            planId: "$circullumId",
            statementId: $(this).data('statementid'),                      
            cafId: "$caf_id",
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
    <h3>Портфолио студентов</h3>
    <?php
    $data = [];
    foreach (is_array($students) ? $students : [$students] as $student) {
        if (isset($student->RecordBook))
            $data[$student->Student->ReferenceId] = $student->Student->ReferenceName;
    }

    echo Html::beginForm(Url::current(), 'get');
    ?>
    <div class="site-index">
        <div class="plan-header" id="plan-header-id">
            <div class="row mb-3">
                <div class="col-12">
                    <label for="plan_plan">Кафедра:</label>
                    <?php echo Html::dropDownList('caf_id', $caf_id, ArrayHelper::map($caf_list, 'id', 'name'), ['class' => 'form-control form-group', 'id' => 'caf_id']); ?>
                </div>

                <div class="col-12">
                    <label for="plan_plan">Учебный план:</label>
                    <?php

                    echo DepDrop::widget([
                        'name' => 'plan_id',
                        'value' => $circullumId,
                        'class' => 'form-control form-group',
                        'data' => $circullum_data,
                        'options' => ['id' => 'plan_id', 'placeholder' => 'Выберите учебный план...'],
                        'type' => DepDrop::TYPE_SELECT2,
                        'select2Options' => ['pluginOptions' => ['allowClear' => false, 'multiple' => false]],
                        'pluginOptions' => [
                            'depends' => ['caf_id'],
                            'dropdownParent' => '#plan-header-id',
                            'placeholder' => 'Выберите учебный план',
                            'url' => Url::to(['/student/evaluation/ap']),
                            'loadingText' => 'Загрузка ...',
                            'initialize' => true,
                        ],
                    ]);


                    ?>
                </div>

                <div class="col-12">
                    <label for="plan_plan">Студент:</label>
                    <?php

                    echo DepDrop::widget([
                        'name' => 'studentId',
                        'value' => $studentId,
                        'class' => 'form-control form-group',
                        'data' => $students,
                        'options' => ['id' => 'studentId', 'placeholder' => 'Выберите студента...'],
                        'type' => DepDrop::TYPE_SELECT2,
                        'select2Options' => ['pluginOptions' => ['allowClear' => false, 'multiple' => false]],
                        'pluginOptions' => [
                            'depends' => ['plan_id', 'caf_id'],
                            'placeholder' => 'Выберите студента',
                            'dropdownParent' => '#plan-header-id',
                            'url' => Url::to(['/student/evaluation/students']),
                            'loadingText' => 'Загрузка ...',
                            'initialize' => true,
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="form-group text-right">
                <?php echo Html::submitButton('Показать', ['class' => 'btn btn-primary']); ?>
            </div>
        </div>
    </div>

    <?php echo Html::endForm(); ?>
</div>

<div class="body-content">
    <div class="row">
        <?php if ($error_RecordBook === false) : ?>
            <div class="col-lg-4">
                <?= Yii::$app->treeParser->treeShow($this, $treeArray); ?>
            </div>

            <div class="col-lg-8">
                <?php Yii::$app->portfolioTable->portfolioTableShow(
                    [
                        'luid'      => $luid,
                        'puid'      => $puid,
                        'marks'     => $marks,
                        'files'     => $files,
                        'caf_id'    => $caf_id,
                        'plan_id'   => $plan_id,
                        'comments'  => $comments,
                        'studentId' => $studentId,
                        'portfolio' => $portfolio
                    ],
                    'evaluation'
                ); ?>
            <?php else : ?>
                <div class="col-lg-12">
                    <?php echo $error_RecordBook; ?>
                </div>
            <?php endif; ?>
            </div>
    </div>
</div>

</div>

<div>
    <?php Modal::begin([
        'headerOptions' => ['id' => 'modalHeader'],
        'id' => 'modal',
        'size' => 'modal-lg',
        'title' => 'Добавление портфолио'
    ]); ?>
    <div id="modalContent"> </div>
    <?php Modal::end(); ?>

    <?php Modal::begin([
        'headerOptions' => ['id' => 'modalHeader'],
        'id' => 'modalComment',
        'size' => 'modal-lg',
        'title' => 'Добавление комментария'
    ]); ?>
    <div id="modalContent"> </div>
    <?php Modal::end(); ?>

    <?php Modal::begin([
        'headerOptions' => ['id' => 'modalHeader'],
        'id' => 'modalMark',
        'size' => 'modal-lg',
        'title' => 'Установка оценки'
    ]); ?>
    <div id="modalContent"> </div>
    <?php Modal::end(); ?>
</div>