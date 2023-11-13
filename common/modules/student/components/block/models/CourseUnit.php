<?php







namespace common\modules\student\components\block\models;

class CourseUnit
{
    public $CourseUnitId;
    public $CourseUnitName;
    public $MaxCount;
    public $Variable;
    public $SubjectsString;

    public function __construct($Subject)
    {
        if (is_array($Subject)) {
            $this->CourseUnitId = $Subject[1];
            $this->CourseUnitName = $Subject[2];
            $this->MaxCount = (int)$Subject[3];
            $this->Variable = (bool)$Subject[9];
            $this->SubjectsString['Subject'] = [];
        } else {
            $this->CourseUnitId = $Subject->CourseUnitId;
            $this->CourseUnitName = $Subject->CourseUnitName;
            $this->MaxCount = $Subject->MaxCount;
            $this->Variable = $Subject->Variable;
        }
    }
}