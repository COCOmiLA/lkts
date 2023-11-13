<?php

namespace common\modules\student\components\schedule;

use common\components\ReferenceTypeManager\ReferenceTypeManager;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;


class ScheduleWidget extends Widget
{
    public $scheduleType;
    public $objectType;
    public $objectId;
    public $startDate;
    public $endDate;
    public $day_button;
    public $another_group;

    public $schedule;
    public $groups;
    public $positions = [
        '' => 'Все должности'
    ];
    public $selected_position = null;

    public function init()
    {
        parent::init();

        $user = Yii::$app->user->identity;

        
        $scheduleLoader = Yii::$app->getModule('student')->scheduleLoader;
        $scheduleLoader->setParams(
            $this->objectType,
            $this->objectId,
            $this->scheduleType,
            $this->startDate,
            $this->endDate,
            $user->userRef,
            $this->another_group
        );

        $schedule_array = $scheduleLoader->loadSchedule($this->selected_position ? json_decode(base64_decode($this->selected_position), true) : null);

        if ($this->objectType == 'Teacher') {
            $states = Yii::$app->getPortfolioService->loadEmployerStates(['PersonRef' => ReferenceTypeManager::GetReference($user->userRef)]);
            if (isset($states) && isset($states->return) && isset($states->return->EmployerState)) {
                if (!is_array($states->return->EmployerState)) {
                    $states->return->EmployerState = [$states->return->EmployerState];
                }

                foreach ($states->return->EmployerState as $state) {
                    $encodedEmployer = base64_encode(json_encode($state->EmployerRef));
                    if (!isset($this->positions[$encodedEmployer])) {
                        $this->positions[$encodedEmployer] = "{$state->PositionRef->ReferenceName} {$state->DepartmentRef->ReferenceName} {$state->JobRateRef->ReferenceName}";
                    }
                }
            }
        }

        $schedule = $schedule_array['data'];
        $this->groups = $schedule_array['groups'];

        if ($schedule != null) {
            $this->schedule = $schedule;
        } else {
            $this->schedule = [];
        }
    }

    public function run()
    {
        if (sizeof($this->schedule) > 0) {
            return $this->render('schedule_widget', [
                'schedule' => $this->schedule,
                'objectType' => $this->objectType,
                'objectId' => $this->objectId,
                'scheduleType' => $this->scheduleType,
                'scheduleStartDate' => $this->startDate,
                'scheduleEndDate' => $this->endDate,
                'groups' => $this->groups,
                'day_button' => $this->day_button,
                'another_group' => $this->another_group,
                'positions' => $this->positions,
                'selected_position' => $this->selected_position,
            ]);
        } else {
            return Html::tag('h4', 'Расписание не найдено');
        }
    }
}
