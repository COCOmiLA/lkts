<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$actualHours = array_filter(
    array_map(
        function ($lesson) {
            if (strlen((string)$lesson->realTimeStart) > 0 && strlen((string)$lesson->realTimeEnd) > 0) {
                return "{$lesson->realTimeStart} - {$lesson->realTimeEnd}";
            }
            return '';
        },
        $lessons
    ),
    function ($realTime) {
        return strlen((string)$realTime) > 0;
    }
);

$hasActualHours = !empty($actualHours);

?>

<div class="table-responsive table-hover">
    <table class="table">
        <thead>
            <tr>
                <th class="table-schedule-hours">
                    Часы
                </th>

                <?php if ($hasActualHours): ?>
                    <th class="table-schedule-hours">
                        Фактические часы
                    </th>
                <?php endif; ?>

                <th class="table-schedule-discipline">
                    Дисциплина
                </th>

                <th class="table-schedule-classroom">
                    Аудитория
                </th>

                <th class="table-schedule-group">
                    Группа
                </th>

                <th class="table-schedule-teacher">
                    Преподаватель
                </th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($lessons as $index => $lesson): ?>
                <tr>
                    <td class="table-schedule-hours">
                        <?= "{$lesson->timeStart} - {$lesson->timeEnd}"; ?>
                    </td>

                    <?php if ($hasActualHours): ?>
                        <td class="table-schedule-hours">
                            <?php $realTime = ArrayHelper::getValue($actualHours, $index);
                            if (isset($realTime)): ?>
                                <?= $realTime; ?>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>

                    <td class="table-schedule-discipline">
                        <?= $lesson->disciplineName; ?> <br> <span class="lesson-type"><?= $lesson->lessonType; ?></span>
                    </td>

                    <td class="table-schedule-classroom">
                        <?php $i = 1;
                        foreach ($lesson->classrooms as $classroom): ?>
                            <?php $url = Url::toRoute([
                                'student/schedule',
                                'scheduleType' => $scheduleType,
                                'objectType' => 'Classroom',
                                'objectId' => $classroom->classroomId,
                                'startDate' => $scheduleStartDate,
                                'endDate' => $scheduleEndDate
                            ]); ?>
                            <a href="<?= $url ?>">
                                <?= $classroom->classroomName; ?>
                            </a>

                            <a href="#" class="classroom-address">
                                <u>
                                    <?= $classroom->classroomAddress; ?>
                                </u>
                            </a>
                            <?php if (sizeof($lesson->classrooms) != $i): ?>
                                ,
                            <?php endif; ?>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    </td>

                    <td class="table-schedule-group">
                        <?php $i = 1;
                        foreach ($lesson->groups as $group): ?>
                            <?php $url = Url::toRoute([
                                'student/schedule',
                                'scheduleType' => $scheduleType,
                                'objectType' => 'AcademicGroup',
                                'objectId' => $group->groupId,
                                'startDate' => $scheduleStartDate,
                                'endDate' => $scheduleEndDate,
                                'another_group' => $group->groupName
                            ]); ?>
                            <a href="<?= $url ?>">
                                <?= $group->groupName; ?>
                            </a>
                            <?php if (sizeof($lesson->groups) != $i): ?>
                                ,
                            <?php endif; ?>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    </td>

                    <td class="table-schedule-teacher">
                        <?php $i = 1;
                        foreach ($lesson->teachers as $teacher): ?>
                            <?php $url = Url::toRoute([
                                'student/schedule',
                                'scheduleType' => $scheduleType,
                                'objectType' => 'Teacher',
                                'objectId' => $teacher->teacherId,
                                'startDate' => $scheduleStartDate,
                                'endDate' => $scheduleEndDate
                            ]); ?>
                            <a href="<?= $url ?>">
                                <?= $teacher->teacherName; ?>
                            </a>
                            <?php if (sizeof($lesson->teachers) != $i): ?>
                                ,
                            <?php endif; ?>
                            <?php $i++; ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>