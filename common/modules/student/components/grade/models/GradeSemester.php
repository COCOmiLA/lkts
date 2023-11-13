<?php

namespace common\modules\student\components\grade\models;








class GradeSemester {
    public $semesterName;
    public $disciplines;
    
    public function __construct($semesterName, $disciplines) {
        $this->semesterName = $semesterName;
        $this->disciplines = $disciplines;
    }
}
