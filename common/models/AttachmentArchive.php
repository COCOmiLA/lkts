<?php

namespace common\models;

use backend\models\UploadableFileTrait;
use common\components\FilesWorker\FilesWorker;
use common\components\ini\iniGet;
use common\models\interfaces\FileToSendInterface;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use Yii;
use yii\behaviors\TimestampBehavior;








class AttachmentArchive extends \yii\db\ActiveRecord implements ICanGetPathToStoreFile, FileToSendInterface
{
    use UploadableFileTrait;

    public $file;

    public static function tableName()
    {
        return '{{%attachment_archive}}';
    }

    public static function getFileRelationTable()
    {
        return '{{%archived_attachments_files}}';
    }

    public static function getFileRelationColumn()
    {
        return 'archived_attachment_id';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'questionary_id',
                    'application_id'
                ],
                'integer'
            ],
            [
                ['attachment_type_id'],
                'required'
            ],
            [
                ['file'],
                'file',
                'extensions' => AttachmentArchive::getExtensionsListForRules(),
                'skipOnEmpty' => true,
                'maxSize' => iniGet::getUploadMaxFilesize(false)
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'file' => Yii::t('abiturient/attachment-archive', 'Подпись для поля "file" формы "Архивный скан документа": `файл`'),
            'filename' => Yii::t('abiturient/attachment-archive', 'Подпись для поля "filename" формы "Архивный скан документа": `Имя файла`'),
            'application_id' => Yii::t('abiturient/attachment-archive', 'Подпись для поля "application_id" формы "Архивный скан документа": `Id заявления`'),
            'questionary_id' => Yii::t('abiturient/attachment-archive', 'Подпись для поля "questionary_id" формы "Архивный скан документа": `Id Анкеты`'),
        ];
    }

    




    public static function getExtensionsListForRules(): string
    {
        return implode(', ', static::getExtensionsList());
    }

    




    public static function getExtensionsList(): array
    {
        return FilesWorker::getAllowedExtensionsToUploadList();
    }

    public function getAbiturientQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id']);
    }

    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    public function getAttachmentType()
    {
        return $this->hasOne(AttachmentType::class, ['id' => 'attachment_type_id']);
    }

    protected function getOwnerId()
    {
        if ($this->questionary_id != null) {
            return $this->abiturientQuestionary->user_id;
        } elseif ($this->application_id != null) {
            return $this->application->user_id;
        } else {
            return false;
        }
    }

}
