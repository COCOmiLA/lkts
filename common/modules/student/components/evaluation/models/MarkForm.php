<?php

namespace common\modules\student\components\evaluation\models;

use yii\base\Model;
use yii\web\UploadedFile;

class MarkForm extends Model
{
    



    public $uid;
    public $luid;
    public $puid;
    public $mark;
    public $type;
    public $planId;
    public $studentId;
    public $cafId;
    public $statementId;

    public function rules()
    {
        return [
            [['studentId', 'uid', 'luid', 'puid', 'planId', 'statementId', 'cafId'], 'safe'],
            [['mark'], 'required'],
            [['type', 'mark'], 'string', 'min' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => 'Файл',
            'mark' => 'Оценка',
            'type' => 'Тип оценки',
        ];
    }
}