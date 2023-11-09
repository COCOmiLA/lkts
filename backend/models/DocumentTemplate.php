<?php

namespace backend\models;

use common\components\AttachmentManager;
use common\components\ini\iniGet;
use common\models\interfaces\FileToSendInterface;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class DocumentTemplate extends ActiveRecord implements ICanGetPathToStoreFile, FileToSendInterface
{
    use UploadableFileTrait;

    public $file;

    public static function tableName()
    {
        return '{{%document_template}}';
    }

    public static function getFileRelationTable()
    {
        return '{{%document_templates_files}}';
    }

    public static function getFileRelationColumn()
    {
        return 'document_template_id';
    }

    public function rules()
    {
        return [
            [['name', 'description'], 'string'],
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
            'name' => 'Наименование документа в системе',
            'description' => 'Описание',
            'file' => 'Документ'
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
