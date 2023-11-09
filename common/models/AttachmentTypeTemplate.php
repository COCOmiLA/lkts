<?php

namespace common\models;

use backend\models\UploadableFileTrait;
use common\models\errors\RecordNotValid;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\File;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\UploadedFile;















class AttachmentTypeTemplate extends ActiveRecord
{
    use UploadableFileTrait;
    use HtmlPropsEncoder;

    public const ACCEPT_FILE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'tif', 'svg', 'bmp', 'psd', 'tiff', 'ai', 'lsm', 'pdf'];

    
    public $file = null;

    protected function getBasePathToStoreFiles()
    {
        return '@storage/web/attachment-type-template/';
    }

    


    public static function tableName()
    {
        return '{{%attachment_type_template}}';
    }

    


    public function rules()
    {
        return [
            [
                [
                    'file_id',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'attachment_type_id',
                ],
                'default',
                'value' => null
            ],
            [
                [
                    'file_id',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'attachment_type_id',
                ],
                'integer'
            ],
            [
                ['attachment_type_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => AttachmentType::class,
                'targetAttribute' => ['attachment_type_id' => 'id']
            ],
            [
                ['file_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => File::class,
                'targetAttribute' => ['file_id' => 'id']
            ],
            [
                ['user_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => User::class,
                'targetAttribute' => ['user_id' => 'id']
            ],
            [
                ['file'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => AttachmentTypeTemplate::getExtensionsListForRules()
            ],
        ];
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function attributeLabels()
    {
        return ['file' => Yii::t('backend', 'Файл шаблона')];
    }

    




    public function getAttachmentType()
    {
        return $this->hasOne(AttachmentType::class, ['id' => 'attachment_type_id']);
    }

    




    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    


    protected function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
    


    protected function getOwnerId(): ?int
    {
        $owner = $this->owner;
        return $owner ? $owner->id : null;
    }

    


    public function getLinkedFile(): ActiveQuery
    {
        return $this->getFile();
    }

    


    private static function getExtensionsListForRules(): string
    {
        return implode(', ', AttachmentTypeTemplate::ACCEPT_FILE_EXTENSIONS);
    }

    


    public function uploadFromPost(): void
    {
        $this->file = UploadedFile::getInstance($this, 'file');

        if (!$this->upload()) {
            throw new RecordNotValid($this);
        }
    }

    


    public function getDownloadUrl(): string
    {
        return Url::to(['/site/download-attachment-type-template', 'id' => $this->id]);
    }

    


    public function getDeleteUrl(): string
    {
        return Url::to(['/site/delete-attachment-type-template', 'id' => $this->id]);
    }
}
