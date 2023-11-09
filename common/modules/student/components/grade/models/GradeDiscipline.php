<?php

namespace common\modules\student\components\grade\models;













class GradeDiscipline {
    public $disciplineName;
    public $gradeDate;
    public $gradeType;
    public $grade;
    public $totalHours;
    public $auditoryHours;
    public $hoursMeasure;
    public $workTheme;
    
    public function __construct($disciplineName, $gradeDate, $gradeType,
            $grade, $totalHours, $auditoryHours, $hoursMeasure, $workTheme) 
    {
        $this->disciplineName = $disciplineName;
        $this->gradeDate = $gradeDate;
        $this->gradeType = $gradeType;
        $this->grade = $grade;
        $this->totalHours = $totalHours;
        $this->auditoryHours = $auditoryHours;
        $this->hoursMeasure = $hoursMeasure;
        $this->workTheme = $workTheme;
    }
}

