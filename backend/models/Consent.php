<?php

namespace backend\models;

use common\components\AttachmentManager;
use common\components\ini\iniGet;
use common\models\interfaces\FileToSendInterface;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;







class Consent extends ActiveRecord implements ICanGetPathToStoreFile, FileToSendInterface
{
    use UploadableFileTrait;

    const ENABLE = 1;
    const DISABLE = 0;
    public $file;

    public static function tableName()
    {
        return '{{%consent}}';
    }

    public static function getFileRelationTable()
    {
        return '{{%consents_files}}';
    }

    public static function getFileRelationColumn()
    {
        return 'consent_id';
    }

    public function rules()
    {
        return [
            [['enable_consent', 'enable_sandbox'], 'integer'],
            [
                ['file'],
                'file',
                'extensions' => 'png, jpg, doc, docx, pdf, bmp, jpeg',
                'skipOnEmpty' => true,
                'skipOnError' => false,
                'maxSize' => iniGet::getUploadMaxFilesize(false)
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'enable_sandbox' => 'Разрешена отправка согласий на зачисление при включенной песочнице',
            'enable_consent' => 'Разрешить добавление и модификацию согласий на зачисление через портал',
            'file' => 'Пустой бланк согласия'
        ];
    }

    public function getMimeType()
    {
        return AttachmentManager::GetMimeType(ArrayHelper::getValue($this, 'linkedFile.extension'));
    }

    protected function getOwnerId()
    {
        return 'admin';
    }

}
