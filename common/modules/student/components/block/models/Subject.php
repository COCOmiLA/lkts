<?php







namespace common\modules\student\components\block\models;

use yii\base\Model;

class Subject extends Model {
    public $CurriculumId;
    public $SubjectId;
    public $SubjectName;
    public $CourseUnitId;
    public $CourseUnitName;
    public $Variable;
    public $Checked;
    public $SaveChecked;
    public $DefaultChecked;

    public function __construct($Subject, $Subject_value) {
        parent::__construct();

        if (is_array($Subject)) {
            $this->CurriculumId = $Subject[4];
            $this->SubjectId = $Subject[5];
            $this->SubjectName = $Subject[6];
            $this->CourseUnitId = $Subject[1];
            $this->CourseUnitName = $Subject[2];
            $this->Variable = (bool)$Subject[9];
            $this->Checked = (bool)$Subject_value;
            $this->SaveChecked = (bool)$Subject[7];
            $this->DefaultChecked = (bool)$Subject[8];
        } else {
            $this->CurriculumId = $Subject->CurriculumId;
            $this->SubjectId = $Subject->SubjectId;
            $this->SubjectName = $Subject->SubjectName;
            $this->CourseUnitId = $Subject->CourseUnitId;
            $this->CourseUnitName = $Subject->CourseUnitName;
            $this->Variable = $Subject->Variable;
            $this->Checked = $Subject->Checked;
            $this->SaveChecked = $Subject->SaveChecked;
            $this->DefaultChecked = $Subject->DefaultChecked;
        }
    }

    public function attributeLabels() {
        return [
            'Checked' => '',
        ];
    }
}
