<?php

namespace common\modules\student\components\academicPlan\models;

class Discipline
{
    public $name;
    public $load;
    public $unit;
    public $code;
    public $period;
    public $amount;
    public $IsControl;

    public function getInfo()
    {
        if ($this->IsControl == true) {
            return '+';
        } else {
            return $this->amount;
        }
    }

    public static function getLoads($disciplines)
    {
        $loads = array_map(function ($o) {
            return trim((string)$o->load);
        }, $disciplines);
        $loads = array_unique($loads);
        return array_values($loads);
    }
}
