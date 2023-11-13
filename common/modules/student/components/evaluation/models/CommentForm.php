<?php

namespace common\modules\student\components\evaluation\models;

use yii\base\Model;
use yii\web\UploadedFile;

class CommentForm extends Model
{
    


    public $uid;
    public $luid;
    public $puid;
    public $comment;
    public $studentId;
    public $caf_id;
    public $plan_id;

    public function rules()
    {
        return [
            [['uid', 'luid', 'puid','studentId', 'caf_id', 'plan_id'], 'safe'],
            ['comment', 'required'],
            ['comment', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'comment' => 'Комментарий',
        ];
    }
}