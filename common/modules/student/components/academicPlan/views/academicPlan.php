<?php

use common\models\User;
use common\modules\student\components\academicPlan\models;
use kartik\grid\GridView;
use kartik\widgets\Alert;
use kartik\widgets\DepDrop;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;

$this->registerJs(
<<<JS
/**
 * Данный кусочек скрипта предназначен для замены кнопки "сабмит". При нажатии на кнопку
 * "Показать" из формы выше собирается POST запрос на отправку в "/student/academicplan",
 * как будто это настоящая кнопка "сабмит". Эот всё ради не перегрузки "DepDrop".
 */
$(document).on('click', 'button#_academic_plan_button', function() {
    var data = $("form#_academic_plan_form").serializeArray();
    $.pjax({
        data : data,
        push : true,
        type : 'POST',
        replace : false,
        timeout : 10000,
        "scrollTo" : false,
        url : '/student/academicplan',
        container : '#_academic_table'
    });
    return false;
});

/**
 * Этот скрипт выполняет тоже, что и выше с "псевдов сабмит", отличие только в том что
 * он добавляет атрибут "sort" в отправляемый POST запрос. Это необходимо потому что
 * встроенный запрос на сортировку конфликтует с запросом от формы, в результате чего
 * при выборе НЕ_ПЕРВОГО элемента из "DepDrop" и нажатие на сортировку, таблица перегружается
 * и отрисовывается уже таблица соответствующая ПЕРВОМУ элементу из "DepDrop". Эту
 * проблему можно решить двумя способами:
 *   1) отправлять форму используя GET;
 *   2) и тот что приведён ниже.
 */
$(document).on('click', 'th a', function() {
    var data = $("form#_academic_plan_form").serializeArray();
    var buffer = $(this);
    var value = buffer[0].text; /** Определяем какой именно был выбран столбец. */
    if (buffer[0].classList.value == "asc") {
        value = "-" + value; /** Если тип сортировки "A-Z", то передаём атрибут для инверсии */
    }
    data.push({ name: "sort", value: value });
    $.pjax({
        data : data,
        push : true,
        type : 'POST',
        replace : false,
        timeout : 10000,
        "scrollTo" : false,
        url : '/student/academicplan',
        container : '#_academic_table'
    });
    return false;
});
JS
    , yii\web\View::POS_END);


$this->title = Yii::$app->name;

echo Alert::widget([
    'type' => Alert::TYPE_INFO,
    'title' => '<strong>Информация</strong>: ',
    'titleOptions' => ['icon' => 'info-sign'],
    'body' => 'Для отображения данных укажите параметры поиска и нажмите кнопку "Показать"'
]);

?>
<div class="site-index">
    <div class="body-content" id="body-content-id">
        <h3>Учебные планы</h3>
        <div class="plan-container">
            <?php if (!empty($plans) && !empty($semesters)) : ?>
                <div class="plan-header">
                    <?php echo Html::beginForm(Url::toRoute(['student/academicplan']), 'post', ['id' => '_academic_plan_form']); ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                                    <?php if (Yii::$app->user->identity->isInRole(User::ROLE_STUDENT)) {
                                        echo Html::tag('label', 'Учебный план:', ['for' => 'plan_plan']);
                                    } elseif (Yii::$app->user->identity->isInRole(User::ROLE_TEACHER)) {
                                        echo Html::tag('label', 'Место работы:', ['for' => 'plan_plan']);
                                    } ?>
                                </div>

                                <div class="col-12 col-sm-8 col-md-9 col-lg-10">
                                    <?php echo Html::dropDownList(
                                        'plan_plan',
                                        $plan_id,
                                        ArrayHelper::map($plans, 'id', 'name'),
                                        ['class' => 'form-control form-group', 'id' => 'plan_id']
                                    ); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="row">
                                <div class="col-12 col-sm-4 col-md-3 col-lg-2">
                                    <label for="plan_plan">Семестр:</label>
                                </div>

                                <div class="col-12 col-sm-8 col-md-9 col-lg-10">
                                    <?php echo DepDrop::widget([
                                        'value' => $semester_id,
                                        'name' => 'plan_semester',
                                        'class' => 'form-control form-group',
                                        'type' => DepDrop::TYPE_SELECT2,
                                        'data' => ArrayHelper::map($semesters, 'id', 'name'),
                                        'options' => ['id' => 'semester_id', 'placeholder' => 'Выберите семестр'],
                                        'select2Options' => ['pluginOptions' => ['allowClear' => false, 'multiple' => false]],
                                        'pluginOptions' => [
                                            'initialize' => true,
                                            'depends' => ['plan_id'],
                                            'loadingText' => 'Загрузка ...',
                                            'placeholder' => 'Выберите семестр',
                                            'dropdownParent' => '#body-content-id',
                                            'url' => Url::to(['/student/semesters']),
                                        ],
                                    ]); ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <?php echo Html::button('Показать', ['class' => 'btn btn-primary', 'id' => '_academic_plan_button']); ?>
                        </div>
                    </div>
                    <?php echo Html::endForm(); ?>
                </div>
            <?php else : ?>
                <?php if (!empty($plans)) {
                    $role = 'данного элемента';
                    if (Yii::$app->user->identity->isInRole(User::ROLE_STUDENT)) {
                        $role = 'учебного плана';
                    } elseif (Yii::$app->user->identity->isInRole(User::ROLE_TEACHER)) {
                        $role = 'места работы';
                    }
                    echo Html::tag(
                        'div',
                        "Для {$role} записей не обнаружено.",
                        ['class' => 'alert alert-info', 'role' => 'alert']
                    );
                } elseif (!empty($semesters)) {
                    echo Html::tag(
                        'div',
                        'Для семестра записей не обнаружено.',
                        ['class' => 'alert alert-info', 'role' => 'alert']
                    );
                } ?>
            <?php endif; ?>
            <div class="row">
                <div class="col-12 disciplines-container">
                    <?php if (isset($disciplines) && $disciplines) {
                        $loads = ['Предмет'];
                        $loads = array_merge($loads, models\Discipline::getLoads($disciplines));
                        $tableArray = [];
                        $disc_name = '';
                        $row['Предмет'] = '';
                        foreach ($loads as $load) {
                            $row[$load] = '';
                        }
                        $empty_row = $row;
                        foreach ($disciplines as $discipline) {
                            if ($disc_name != $discipline->name && $disc_name != '') {
                                $tableArray[] = $row;
                                $row = $empty_row;
                            }
                            $row['Единица измерения'] = $discipline->unit;
                            if ($row['Предмет'] == '') {
                                $row['Предмет'] = $discipline->name;
                            }
                            foreach ($loads as $load) {
                                if ($discipline->load == $load) {
                                    $row[$load] = $discipline->getInfo();
                                }
                            }
                            $disc_name = $discipline->name;
                        }
                        $tableArray[] = $row;

                        $loads[] = 'Единица измерения';
                        $dataProvider = new ArrayDataProvider([
                            'allModels' => $tableArray,
                            'sort' => [
                                'attributes' => $loads
                            ]
                        ]);
                        $columns = [];
                        foreach ($loads as $load) {
                            $columns[] = [
                                'label' => $load,
                                'attribute' => $load,
                                'contentOptions' => ($load == 'Предмет') ? ['id' => '_academic_table-left', 'class' => 'col-sm-4'] : []
                            ];
                        }

                        Pjax::begin([
                            'options' => ['id' => '_academic_table'],
                            'timeout' => false,
                            'enablePushState' => false,
                            'clientOptions' => ['method' => 'POST']
                        ]);
                        echo Html::beginTag('div', ['class' => 'table-responsive']);
                        echo GridView::widget([
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
                            'columns' => $columns,
                            'layout' => '{items}{pager}',
                            'dataProvider' => $dataProvider,
                            'options' => ['align' => 'left', 'id' => '_academic_table']
                        ]);
                        echo Html::endTag('div');
                        Pjax::end();
                    } else {
                        Pjax::begin([
                            'options' => ['id' => '_academic_table'],
                            'timeout' => false,
                            'enablePushState' => false,
                            'clientOptions' => ['method' => 'POST']
                        ]);
                        echo Html::tag(
                            'div',
                            'Для заданных параметров данные не могут быть предоставлены.',
                            ['class' => 'alert alert-info', 'role' => 'alert']
                        );
                        Pjax::end();
                    } ?>
                </div>
            </div>
        </div>
    </div>
</div>