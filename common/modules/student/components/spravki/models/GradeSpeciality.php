<?php

namespace common\modules\student\components\grade\models;











class GradeSpeciality {
    
    public $facultyName;
    public $studyForm;
    public $specialityName;
    public $specializationName;
    public $courseName;
    
    public $semesters;
    
    public function __construct($facultyName, $studyForm, $specialityName, $specializationName, $courseName, $semesters) {
        $this->facultyName = $facultyName;
        $this->studyForm = $studyForm;
        $this->specialityName = $specialityName;
        $this->specializationName = $specializationName;
        $this->courseName = $courseName;
        
        $this->semesters = $semesters;
    }
}
