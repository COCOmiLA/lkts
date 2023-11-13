<?php

namespace common\modules\abiturient\modules\admission\models;

class ListTotalAbitBudgetCountRow{
    public $department;
    public $speciality_code;
    public $speciality_name;
    public $admission_plan;
    public $abiturient_count;
    public $doc_original_count;
    public $application_count;
    
    public function getFullSpec(){
        return $this->speciality_code.' '.$this->speciality_name;
    }
}
