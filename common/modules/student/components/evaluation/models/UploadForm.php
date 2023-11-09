<?php

namespace common\modules\student\components\evaluation\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    


    public $file;
    public $uid;
    public $luid;
    public $puid;
    public $description;
    public $studentId;
    public $caf_id;

    public function rules()
    {
        return [
            [['file'], 'file', 'skipOnEmpty' => false, 'extensions' => static::getExtensionsListForRules()],

            [['uid', 'luid', 'puid', 'studentId', 'caf_id'], 'safe'],
            ['description', 'string'],
            ['file', 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => 'Файл',
            'description' => 'Описание',
        ];
    }

    




    public static function getExtensionsListForRules(): string
    {
        return implode(', ', static::getExtensionsList());
    }

    




    public static function getExtensionsList(): array
    {
        return [
            'png',
            'bmp',
            'pdf',
            'txt',
            'jpg', 'jpeg',
            'ppt', 'pptx', 'odp',
            'doc', 'docx', 'odt',
            'xls', 'xlsx', 'ods',
        ];
    }
}
