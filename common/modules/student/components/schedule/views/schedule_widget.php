<?php

use common\modules\student\components\schedule\assets\ScheduleAsset;
use common\modules\student\components\schedule\ScheduleLoader;
use sguinfocom\DatePickerMaskedWidget\DatePickerMaskedWidget;
use yii\bootstrap4\Carousel;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;

$defaultDateStart = '';
$defaultDateEnd = '';

if (isset($schedule[0])) {
    if ($schedule[0]->week != null) {
        $defaultDateEnd = $schedule[0]->week->dateEnd;
        $defaultDateStart = $schedule[0]->week->dateStart;
    } else {
        $defaultDateEnd = ArrayHelper::getValue($schedule, '0.project.0.days.0.date') ?? date(ScheduleLoader::DATA_TO_WEB);
        $defaultDateStart = ArrayHelper::getValue($schedule, '0.project.0.days.0.date') ?? date(ScheduleLoader::DATA_TO_WEB);
    }
}

$index = 0;

if ($day_button != null && count($groups) > 0 && $day_button != 'undefined') {
    $id_group = explode('-', $day_button)[1];
    foreach ($groups as $i => $group) {
        if ($id_group == $group['id']) {
            $index = $i;
            break;
        }
    }
} elseif ($objectId != '') {
    foreach ($schedule as $i => $sch) {
        if ($sch->objectId == $objectId) {
            $index = $i;
            break;
        }
    }
} else {
    $day_button = '';
}

ScheduleAsset::register($this);

$tmplurl1 = Url::toRoute([
    'student/schedule',
    'scheduleType' => 'Full',
    'objectType' => $objectType,
    'objectId' => $objectId,
    'startDate' => 'sdate',
    'endDate' => 'edate',
    'day_button' => 'scId',
    'another_group' => 'a_group'
]);

$tmplurl2 = Url::toRoute([
    'student/schedule',
    'scheduleType' => 'Week',
    'objectType' => $objectType,
    'objectId' => $objectId,
    'startDate' => 'sdate',
    'endDate' => 'edate',
    'day_button' => 'scId',
    'another_group' => 'a_group'
]);

$tmplurl3 = Url::toRoute([
    'student/schedule',
    'scheduleType' => 'Full',
    'objectType' => $objectType,
    'objectId' => $objectId,
    'startDate' => 'sdate',
    'endDate' => 'edate',
    'day_button' => 'scId',
    'another_group' => 'a_group'
]);

$tmplurl4 = Url::toRoute([
    'student/schedule',
    'scheduleType' => 'Week',
    'objectType' => $objectType,
    'objectId' => $objectId,
    'startDate' => 'sdate',
    'endDate' => 'edate',
    'day_button' => 'scId',
    'another_group' => 'a_group'
]);

$tmplurl = Url::toRoute([
    'student/schedule',
    'scheduleType' => 'Week',
    'objectType' => $objectType,
    'objectId' => $objectId,
    'startDate' => 'sdate',
    'endDate' => 'edate',
    'day_button' => 'scId',
    'another_group' => 'a_group'
]);

$script = '
    $(document).ready(function () {
        $("#group-select").change(function () {
            var scId = $("#group-select option:selected").val();
            $(".schedule-container").hide();
            $("#" + scId).fadeIn();
            var s = $("#group-select option:selected").text();

            var a = window.location.href;
            if (a.indexOf("Full") >= 0) {
                var tmplurl1 = "' . $tmplurl1 . '";

                var sdate = $("#datetimepicker3").val();
                var tplurl = tmplurl1.replace("scId", scId);
                var turl = tplurl.replace("sdate", sdate);
                var url = turl.replace("edate", sdate);
                var u = url.replace("a_group", s);

                $("#daytype").attr("href", u);
                var tmplurl2 = "' . $tmplurl2 . '";

                var sdate = $("#datetimepicker3").val();
                var tplurl = tmplurl2.replace("scId", scId);
                var turl = tplurl.replace("sdate", sdate);
                var url = turl.replace("edate", sdate);
                var u = url.replace("a_group", s);

                $("#weektype").attr("href", u);
            } else {
                var tmplurl3 = "' . $tmplurl3 . '";

                var sdate = $("#datetimepicker1").val();
                var edate = $("#datetimepicker2").val();
                var tplurl = tmplurl3.replace("scId", scId);
                var turl = tplurl.replace("sdate", sdate);
                var url = turl.replace("edate", edate);
                var u = url.replace("a_group", s);

                $("#daytype").attr("href", u);
                var tmplurl4 = "' . $tmplurl4 . '";
                var sdate = $("#datetimepicker1").val();
                var edate = $("#datetimepicker2").val();
                var tplurl = tmplurl4.replace("scId", scId);
                var turl = tplurl.replace("sdate", sdate);
                var url = turl.replace("edate", edate);
                var u = url.replace("a_group", s);

                $("#weektype").attr("href", u);
            }
        });
        
        $("#position-select").change(
            function () {
                var selected_position = $("#position-select option:selected").val();
                $(".schedule-container").hide();
                
                // set selected_position parameter to current url and go to it
                var url = new URL(window.location.href);
                url.searchParams.set("selected_position", selected_position);
                window.location.href = url
            }
        );
        
        $("#week-date-update").click(function (e) {
            var scId = $("#group-select option:selected").val();
            var s = $("#group-select option:selected").text();
            var tmplurl = "' . $tmplurl . '";
            var sdate = $("#week-date-update a").data("startdate");
            var edate = $("#week-date-update a").data("enddate");
            var tplurl = tmplurl.replace("scId", scId);
            var turl = tplurl.replace("sdate", sdate);
            var url = turl.replace("edate", edate);
            var u = url.replace("a_group", s);
            window.location = u;
            return false;
        });
    });
';

$this->registerJs($script, View::POS_END);

?>

<div class="schedule-widget">
    <div class="schedule-head">
        <div class="row">
            <div class="col-md-9 col-sm-7 col-12">
                <div class="schedule-object">
                    <div class="row">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-12">
                                    <?php if ($schedule[0]->objectType == "AcademicGroup") :
                                        if (isset($groups) && count($groups) > 1) : ?>
                                            <label for="group-select">
                                                Расписание занятий группы
                                            </label>

                                            <select id="group-select" class="form-control">
                                                <?php foreach ($groups as $idx => $group) : ?>
                                                    <option
                                                        value="sch-<?= $group['id']; ?>" <?php if ($index == $idx) : ?> selected <?php endif; ?>>
                                                        <?= $group['name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php else : ?>
                                            <label for="group-select">
                                                Расписание занятий группы <?= $schedule[0]->objectName; ?>
                                            </label>
                                        <?php endif; ?>
                                    <?php elseif ($schedule[0]->objectType == "Teacher") : ?>
                                        <label for="position-select">
                                            Расписание занятий преподавателя <?= $schedule[0]->objectName; ?>
                                        </label>
                                        <?php if ($positions): ?>
                                            <select id="position-select" class="form-control">
                                                <?php foreach ($positions as $idx => $position) : ?>
                                                    <option
                                                        value="<?= $idx; ?>" <?php if ($selected_position == $idx) : ?> selected <?php endif; ?>>
                                                        <?= $position; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    <?php elseif ($schedule[0]->objectType == "Classroom") : ?>
                                        <label for="group-select">
                                            Расписание занятий в аудитории <?= $schedule[0]->objectName; ?>
                                        </label>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(common\models\User::ROLE_STUDENT)) :
                                if ($schedule && (count($schedule) > 1 || $schedule[0]->objectType != 'AcademicGroup')) {
                                    
                                    
                                    $url = Url::toRoute([
                                        'student/schedule',
                                        'scheduleType' => 'Week',
                                        'objectType' => 'AcademicGroup',
                                        'objectId' => $schedule[0]->objectId,
                                        'startDate' => $scheduleStartDate,
                                        'endDate' => $scheduleEndDate
                                    ]); ?>
                                    <a id="user-schedule" href="<?= $url ?>">
                                        Вернуться к моему расписанию
                                    </a>
                                <?php } ?>
                            <?php elseif (isset(Yii::$app->user->identity) && Yii::$app->user->identity->isInRole(common\models\User::ROLE_TEACHER)) :
                                if ($schedule && ($schedule[0]->objectType != 'Teacher' || $schedule[0]->objectId != Yii::$app->user->identity->userRef->reference_id)) {
                                    
                                    $url = Url::toRoute([
                                        'student/schedule',
                                        'scheduleType' => 'Week',
                                        'objectType' => 'Teacher',
                                        'endDate' => $scheduleEndDate,
                                        'startDate' => $scheduleStartDate,
                                    ]); ?>
                                    <a id="user-schedule" href="<?= $url ?>">Вернуться к моему расписанию</a>
                                <?php } ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 col-sm-5 col-12">
                <div class="schedule-period">
                    <div class="d-flex flex-row-reverse">
                        <div class="p-2">
                            <?php if ($schedule[0]->week != null) : ?>
                                <p class="schedule-type-switch">
                                    <?php $url = Url::toRoute([
                                        'student/schedule',
                                        'scheduleType' => 'Full',
                                        'objectType' => $objectType,
                                        'objectId' => $objectId,
                                        'startDate' => $scheduleStartDate,
                                        'endDate' => $scheduleEndDate,
                                        'day_button' => $day_button,
                                        'another_group' => $another_group,
                                    ]); ?>
                                    <a href="<?= $url ?>" id="daytype" class="schedule-day-type"><u
                                            class="<?php if ($scheduleType == "Full") : ?>active<?php endif; ?>">День</u></a>
                                    <span class="schedule-type-divider">/</span>
                                    <?php $url = Url::toRoute([
                                        'student/schedule',
                                        'scheduleType' => 'Week',
                                        'objectType' => $objectType,
                                        'objectId' => $objectId,
                                        'startDate' => $scheduleStartDate,
                                        'endDate' => $scheduleEndDate,
                                        'day_button' => $day_button,
                                        'another_group' => $another_group
                                    ]); ?>
                                    <a href="<?= $url ?>" id="weektype" class="schedule-week-type"><u
                                            class="<?php if ($scheduleType == "Week") : ?>active<?php endif; ?>">Неделя</u></a>
                                </p>
                                <label for="datetimepicker1">с</label>
                                <?= DatePickerMaskedWidget::widget(
                                    [
                                        'name' => 'datetimepicker1',
                                        'value' => $defaultDateStart,
                                        'inline' => false,
                                        'language' => 'ru',
                                        'template' => '{input}{addon}',
                                        'clientOptions' => [
                                            'clearBtn' => false,
                                            'weekStart' => '1',
                                            'autoclose' => true,
                                            'todayBtn' => 'linked',
                                            'format' => 'dd.mm.yyyy',
                                            'calendarWeeks' => 'true',
                                            'todayHighlight' => 'true',
                                            'orientation' => 'top left',
                                            'daysOfWeekDisabled' => [2, 3, 4, 5, 6, 0],
                                        ],
                                        'clientEvents' => [
                                            'changeDate' => '
                                function(e) {
                                    var sdate = $("#datetimepicker1").val();
                                    if (sdate.length > 0) {
                                        var edate = $("#datetimepicker2").val();
                                
                                        var from = sdate.split(".");
                                        var curr = new Date(from[2], from[1] - 1, from[0]);
                                
                                        var first = curr.getDate() - curr.getDay() + 1;
                                        var last = first + 6;
                                
                                        var firstday = new Date(curr.setDate(first)); //.toUTCString();
                                        var lastday = new Date(curr.setDate(last)); //.toUTCString();
                                
                                        var month = lastday.getMonth() + 1;
                                        var day = lastday.getDate();
                                        var year = lastday.getFullYear();
                                
                                        var dateStr = ("0" + day).slice(-2) + "." + ("0" + month).slice(-2) + "." + year;
                                        if (dateStr != edate) {
                                            $("#datetimepicker2").val(dateStr);
                                        }
                                    } else {
                                        $("#datetimepicker2").val(sdate);
                                    }
                                    var sdate = $("#datetimepicker1").val();
                                    var edate = $("#datetimepicker2").val();
                                
                                    $("#week-date-update a").data("startdate", sdate);
                                    $("#week-date-update a").data("enddate", edate);
                                    $("#week-date-update").show();
                                }
                            ',
                                        ],
                                        'options' => [
                                            'id' => 'datetimepicker1',
                                        ],
                                        'maskOptions' => [
                                            'alias' => 'dd.mm.yyyy'
                                        ],
                                    ]
                                ); ?>
                                <br/>
                                <label for="datetimepicker2" id="datepicker2-label">
                                    по
                                </label>
                                <?= DatePickerMaskedWidget::widget(
                                    [
                                        'name' => 'datetimepicker2',
                                        'value' => $defaultDateEnd,
                                        'inline' => false,
                                        'language' => 'ru',
                                        'template' => '{input}{addon}',
                                        'clientOptions' => [
                                            'clearBtn' => false,
                                            'weekStart' => '1',
                                            'autoclose' => true,
                                            'todayBtn' => 'linked',
                                            'format' => 'dd.mm.yyyy',
                                            'calendarWeeks' => 'true',
                                            'todayHighlight' => 'true',
                                            'orientation' => 'top left',
                                            'daysOfWeekDisabled' => [2, 3, 4, 5, 6, 1],
                                        ],
                                        'clientEvents' => [
                                            'changeDate' => '
                                function(e) {
                                    var sdate = $("#datetimepicker1").val();
                                    var edate = $("#datetimepicker2").val();
                                    if (edate.length > 0) {
                                        var from = edate.split(".");
                                        var curr = new Date(from[2], from[1] - 1, from[0]);

                                                var first = curr.getDate() - curr.getDay() - 6;
                                                var last = first + 6;

                                                var firstday = new Date(curr.setDate(first)); //.toUTCString();
                                                var lastday = new Date(curr.setDate(last)); //.toUTCString();

                                                var month = firstday.getMonth() + 1;
                                                var day = firstday.getDate();
                                                var year = firstday.getFullYear();
                                                var dateStr = ("0" + day).slice(-2) + "." + ("0" + month).slice(-2) + "." + year;
                                                if (dateStr != sdate) {
                                                    $("#datetimepicker1").val(dateStr);
                                                }
                                            } else {
                                                $("#datetimepicker1").val(edate);
                                            }
                                            var sdate = $("#datetimepicker1").val();
                                            var edate = $("#datetimepicker2").val();

                                            $("#week-date-update a").data("startdate", sdate);
                                            $("#week-date-update a").data("enddate", edate);
                                            $("#week-date-update").show();
                                        }
                                    ',
                                        ],
                                        'options' => [
                                            'id' => 'datetimepicker2'
                                        ],
                                        'maskOptions' => [
                                            'alias' => 'dd.mm.yyyy'
                                        ],
                                    ]
                                ); ?>
                                <div id="week-date-update" class="date-updater">
                                    <a href="#" class="btn btn-primary" data-startdate="<?= $scheduleStartDate; ?>"
                                       data-enddate="<?= $scheduleEndDate; ?>">
                                        Показать
                                    </a>
                                </div>
                            <?php elseif (isset($schedule[0]->project[0], $schedule[0]->project[0]->days)) : ?>
                                <?php $url = Url::toRoute([
                                    "student/schedule",
                                    "scheduleType" => "Full",
                                    "objectType" => $objectType,
                                    "objectId" => $objectId,
                                    "startDate" => "sdate",
                                    "endDate" => "edate",
                                    "day_button" => "scId",
                                    "another_group" => "a_group"
                                ]);
                                $script = '
                            $(document).ready(function () {
                                var startDate = "' . $scheduleStartDate . '";
                                var from = startDate.split(".");
                                var sDate = new Date(from[2], from[1] - 1, from[0]);

                                $("#day-date-update").click(function (e) {
                                    var scId = $("#group-select option:selected").val();

                                    var s = $("#group-select option:selected").text();

                                    var tmplurl = "' . $url . '";

                                    var sdate = $("#day-date-update a").data("startdate");
                                    var edate = $("#day-date-update a").data("enddate");
                                    var tplurl = tmplurl.replace("scId", scId);
                                    var turl = tplurl.replace("sdate", sdate);
                                    var url = turl.replace("edate", edate);
                                    var u = url.replace("a_group", s);
                                    window.location = u;
                                    return false;
                                });
                            });
                        ';

                                $this->registerJs($script, View::POS_END);
                                ?>

                                <p class="schedule-type-switch">
                                    <?php $url = Url::toRoute([
                                        'student/schedule',
                                        'scheduleType' => 'Full',
                                        'objectType' => $objectType,
                                        'objectId' => $objectId,
                                        'startDate' => $scheduleStartDate,
                                        'endDate' => $scheduleEndDate,
                                        'day_button' => $day_button,
                                        'another_group' => $another_group
                                    ]); ?>
                                    <a href="<?= $url; ?>" id="daytype" class="schedule-day-type">
                                        <u class="<?php if ($scheduleType == "Full") : ?>active<?php endif; ?>">
                                            День
                                        </u>
                                    </a>
                                    <span class="schedule-type-divider">/</span>
                                    <?php $url = Url::toRoute([
                                        'student/schedule',
                                        'scheduleType' => 'Week',
                                        'objectType' => $objectType,
                                        'objectId' => $objectId,
                                        'startDate' => $scheduleStartDate,
                                        'endDate' => $scheduleEndDate,
                                        'day_button' => $day_button,
                                        'another_group' => $another_group
                                    ]); ?>
                                    <a href="<?= $url ?>" id="weektype" class="schedule-week-type">
                                        <u class="<?php if ($scheduleType == "Week") : ?>active<?php endif; ?>">
                                            Неделя
                                        </u>
                                    </a>
                                </p>
                                <?php if ($objectType == 'AcademicGroup' && count($groups) > 0) {
                                    $day_widget_id = $groups[0]['id'];
                                } else {
                                    $day_widget_id = $objectId;
                                } ?>
                                <?= DatePickerMaskedWidget::widget(
                                    [
                                        'name' => 'datetimepicker3',
                                        'value' => $scheduleStartDate,
                                        'inline' => false,
                                        'language' => 'ru',
                                        'template' => '{input}{addon}',
                                        'clientOptions' => [
                                            'clearBtn' => false,
                                            'weekStart' => '1',
                                            'autoclose' => true,
                                            'todayBtn' => 'linked',
                                            'format' => 'dd.mm.yyyy',
                                            'calendarWeeks' => 'true',
                                            'todayHighlight' => 'true',
                                            'orientation' => 'top left',
                                            'daysOfWeekDisabled' => [0],
                                        ],
                                        'clientEvents' => [
                                            'changeDate' => 'function(e) {
                                    var sdate = $("#datetimepicker3").val();
                                    $("#day-date-update a").data("startdate",sdate);
                                    $("#day-date-update a").data("enddate",sdate); 
                                    $("#day-date-update").show();
                                }',
                                        ],
                                        'options' => [
                                            'id' => 'datetimepicker3',
                                            'class' => 'form-control krajee-datepicker',
                                        ],
                                        'maskOptions' => [
                                            'alias' => 'dd.mm.yyyy'
                                        ],
                                    ]
                                );
                                ?>
                                <div id="day-date-update" class="date-updater">
                                    <a href="#" class="btn btn-primary" data-startdate="<?= $scheduleStartDate; ?>"
                                       data-enddate="<?= $scheduleStartDate; ?>">
                                        Показать
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="schedule-body">
            <?php if ($schedule[0]->week != null) :
                foreach ($schedule as $schKey => $sch) :
                    if ($objectType == 'AcademicGroup' && count($groups) > 0 && count($groups) > $schKey) {
                        $day_widget_id = $groups[$schKey]['id'];
                    } else {
                        $day_widget_id = $objectId;
                    }
                    if ($sch->week && $sch->week->project && count($sch->week->project) > 0) : ?>
                        <?php $moreThanOne = false;
                        $weekItems = [];
                        if (count($sch->week->project) > 1) {
                            $moreThanOne = true;
                        } ?>
                        <div class="week-schedule schedule-container week-container <?php if ($index == $schKey) {
                            echo 'active';
                        } ?>">
                            <?php foreach ($sch->week->project as $project) : ?>
                                <?php if (count($project->days) > 0) : ?>
                                    <?php if ($moreThanOne) : ?>
                                        <?php $scheduleTable = "
                                        <div class=\"alert alert-info project-title\">
                                            <div style=\"text-align: center;\">
                                                {$project->projectName}
                                            </div>
                                        </div>
                                    "; ?>
                                    <?php else : ?>
                                        <?php $scheduleTable = ''; ?>
                                    <?php endif; ?>
                                    <?php foreach ($project->days as $day) : ?>
                                        <?php $table = $this->render('_scheduleTable', [
                                            'lessons' => $day->lessons,
                                            'objectType' => $objectType,
                                            'objectId' => $objectId,
                                            'scheduleType' => $scheduleType,
                                            'scheduleStartDate' => $scheduleStartDate,
                                            'scheduleEndDate' => $scheduleEndDate,
                                            'another_group' => $another_group
                                        ]);
                                        $scheduleTable .= "<div class=\"day-container\">
                                        <strong class=\"day-name\">
                                            {$day->dayName} {$day->date}
                                        </strong>
                                        {$table}
                                    </div>"; ?>
                                    <?php endforeach; ?>
                                    <?php $weekItems[] = "$scheduleTable"; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <?php if ($moreThanOne) {
                                echo Carousel::widget([
                                    'clientOptions' => ['interval' => false],
                                    'items' => $weekItems,
                                ]);
                            } else {
                                echo implode('', $weekItems);
                            } ?>
                        </div>
                    <?php else : ?>
                        <?php if ($index == $schKey) : ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        Для заданных параметров данные не могут быть предоставлены.
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php elseif ($schedule[0]->project != null) : ?>
                <?php foreach ($schedule as $schKey => $sch) :
                    if ($objectType == 'AcademicGroup' && count($groups) > 0 && count($groups) > $schKey) {
                        $day_widget_id = $groups[$schKey]['id'];
                    } else {
                        $day_widget_id = $objectId;
                    } ?>
                    <?php $moreThanOne = false;
                    $weekItems = [];
                    if (count($sch->project) > 1) {
                        $moreThanOne = true;
                    } ?>
                    <div class="schedule-container day-outer-container <?php if ($index == $schKey) {
                        echo 'active';
                    } ?>" id="sch-<?= str_replace(":", "", $day_widget_id); ?>">
                        <?php foreach ($sch->project as $project) : ?>
                            <?php if (count($project->days) > 0) : ?>
                                <?php if ($moreThanOne) : ?>
                                    <?php $scheduleTable = "
                                        <div class=\"alert alert-info project-title\">
                                            <div style=\"text-align: center;\">
                                                {$project->projectName}
                                            </div>
                                        </div>
                                    "; ?>
                                <?php else : ?>
                                    <?php $scheduleTable = ''; ?>
                                <?php endif; ?>
                                <?php foreach ($project->days as $day) : ?>
                                    <?php $table = $this->render('_scheduleTable', [
                                        'lessons' => $day->lessons,
                                        'objectType' => $objectType,
                                        'objectId' => $objectId,
                                        'scheduleType' => $scheduleType,
                                        'scheduleStartDate' => $scheduleStartDate,
                                        'scheduleEndDate' => $scheduleEndDate,
                                        'day_button' => $day_button,
                                        'another_group' => $another_group
                                    ]);
                                    $scheduleTable .= "<div class=\"day-container\">
                                        <strong class=\"day-name\">
                                            {$day->dayName} {$day->date}
                                        </strong>
                                        {$table}
                                    </div>"; ?>
                                <?php endforeach; ?>
                                <?php $weekItems[] = "$scheduleTable"; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if ($moreThanOne) {
                            echo Carousel::widget([
                                'clientOptions' => ['interval' => false],
                                'items' => $weekItems,
                            ]);
                        } else {
                            echo implode('', $weekItems);
                        } ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>