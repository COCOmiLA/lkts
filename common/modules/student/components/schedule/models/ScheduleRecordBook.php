<?php

namespace common\modules\student\components\schedule\models;

class SchedulePair {
    public string $teacher;
    public string $group;
    public string $subj_name;
    public int $type_of_week;
    public string $day_of_week;
    public int $time;
    public string $classroom;

    /**
     * @param string $teacher
     * @param string $group
     * @param string $subj_name
     * @param int $type_of_week
     * @param string $day_of_week
     * @param int $time
     */
    public function __construct(string $teacher, string $group, string $subj_name, int $type_of_week, string $day_of_week, int $time, string $classroom)
    {
        $this->teacher = $teacher;
        $this->group = $group;
        $this->subj_name = $subj_name;
        $this->type_of_week = $type_of_week;
        $this->day_of_week = $day_of_week;
        $this->time = $time;
        $this->classroom = $classroom;
    }


}