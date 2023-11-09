<?php

namespace common\modules\student\components\portfolio\models;

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
    public $statementId;

    public function rules()
    {
        return [
            [['studentId', 'uid', 'luid', 'puid', 'planId', 'statementId'], 'safe'],
            [['type', 'mark'], 'string'],
            ['mark', 'required']
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
