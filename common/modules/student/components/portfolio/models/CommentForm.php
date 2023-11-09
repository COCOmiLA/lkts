<?php

namespace common\modules\student\components\portfolio\models;

use yii\base\Model;
use yii\web\UploadedFile;

class CommentForm extends Model
{
    


    public $uid;
    public $luid;
    public $puid;
    public $comment;
    public $studentId;
    public $recordbook_id;

    public function rules()
    {
        return [
            [['uid', 'luid', 'puid','studentId', 'recordbook_id'], 'safe'],
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