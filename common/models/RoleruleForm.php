<?php

namespace common\models;

use yii\base\Model;

class RoleruleForm extends Model
{
    public $student;
    public $teacher;
    public $abiturient;

    public function rules()
    {
        return [
            ['student', 'integer',],
            ['teacher', 'integer',],
            ['abiturient', 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'student' => 'Разрешить вход в личный кабинет студенту',
            'teacher' => 'Разрешить вход в личный кабинет преподавателю',
            'abiturient' => 'Разрешить вход в личный кабинет поступающим и модераторам'
        ];
    }
}
