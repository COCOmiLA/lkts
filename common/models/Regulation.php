<?php

namespace common\models;

use backend\models\UploadableFileTrait;
use common\components\ini\iniGet;
use common\models\interfaces\FileToSendInterface;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

























class Regulation extends ActiveRecord implements ICanGetPathToStoreFile, FileToSendInterface
{
    use UploadableFileTrait;

    const CONTENT_TYPE_LINK = 1;
    const CONTENT_TYPE_HTML = 2;
    const CONTENT_TYPE_FILE = 3;

    


    public $file;

    const FILE_PATH = '@storage/web/regulations/';

    


    public static function tableName()
    {
        return '{{%regulation}}';
    }

    public static function getFileRelationTable()
    {
        return '{{%regulations_files}}';
    }

    public static function getFileRelationColumn()
    {
        return 'regulation_id';
    }

    


    public function rules()
    {
        return [
            [['content_type', 'attachment_type'], 'integer'],
            [['confirm_required',], 'boolean'],
            [['name', 'confirm_required', 'content_type', 'related_entity'], 'required'],
            [['related_entity', 'name', 'content_link'], 'string', 'max' => 255],
            [['before_link_text'], 'string', 'max' => 1000],
            [['content_html'], 'string', 'max' => 10000],
            [['file'], 'required', 'when' => function ($model) {
                return $model->isFileContent() && $model->isNewRecord;
            }, 'whenClient' => "function() {
                if(!$('#regulation-id').val()) {
                    var select = $(\"#content_type_field\")[0]
                    var selected = select.options[select.selectedIndex].value;
                    return +selected === " . self::CONTENT_TYPE_FILE . ".
                }
                return false;
            }"],
            [['content_link'], 'required', 'when' => function ($model) {
                return $model->isLinkContent();
            }, 'whenClient' => "function() {
                var select = $(\"#content_type_field\")[0]
                var selected = select.options[select.selectedIndex].value;
                return +selected === " . self::CONTENT_TYPE_LINK . ".
            }"],
            [['content_html'], 'required', 'when' => function ($model) {
                return $model->isHTMLContent();
            }, 'whenClient' => "function() {
                var select = $(\"#content_type_field\")[0]
                var selected = select.options[select.selectedIndex].value;
                return +selected === " . self::CONTENT_TYPE_HTML . ".
            }"],
            [['file'], 'file', 'extensions' => 'png, jpg, doc, docx, pdf, bmp, jpeg', 'skipOnEmpty' => !$this->isFileContent() || !$this->isNewRecord, 'maxSize' => iniGet::getUploadMaxFilesize(false)],
            [['attachment_type'], 'exist', 'skipOnError' => true, 'targetClass' => AttachmentType::class, 'targetAttribute' => ['attachment_type' => 'id']],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'related_entity' => 'Связанная сущность',
            'confirm_required' => 'Требовать подтверждение ознакомления',
            'before_link_text' => 'Текст перед ссылкой',
            'name' => 'Имя ссылки',
            'content_type' => 'Тип содержимого ссылки',
            'content_link' => 'Адрес ссылки',
            'content_html' => 'HTML-текст',
            'content_file' => 'Файл',
            'file' => 'Файл',
            'attachment_type' => 'Требуемый тип скан-копии',
        ];
    }

    




    public function getAttachmentType()
    {
        return $this->hasOne(AttachmentType::class, ['id' => 'attachment_type']);
    }

    public static function getContentTypes()
    {
        return [
            self::CONTENT_TYPE_HTML => 'HTML текст',
            self::CONTENT_TYPE_FILE => 'Файл',
            self::CONTENT_TYPE_LINK => 'Ссылка'
        ];
    }

    public function getContent_file_extension(): ?string
    {
        return ArrayHelper::getValue($this, 'extension');
    }

    public function getContent_file(): ?string
    {
        return ArrayHelper::getValue($this, 'filename');
    }

    public function isFileContent()
    {
        return (int)$this->content_type === self::CONTENT_TYPE_FILE;
    }

    public function isLinkContent()
    {
        return (int)$this->content_type === self::CONTENT_TYPE_LINK;
    }

    public function isHTMLContent()
    {
        return (int)$this->content_type === self::CONTENT_TYPE_HTML;
    }

    public function getConfirmRequiredText()
    {
        return $this->confirm_required ? 'Да' : 'Нет';
    }

    public function getContentTypeText()
    {
        return self::getContentTypes()[$this->content_type];
    }

    public function getMimeType()
    {
        switch ($this->content_file_extension) {
            case 'pdf':
                return 'application/pdf';
                break;
            default:
                return 'image/jpeg';
                break;
        }
    }

    public function getUserRegulations()
    {
        return $this->hasMany(UserRegulation::class, ['regulation_id' => 'id']);
    }

    protected function getOwnerId()
    {
        return 'admin';
    }

    protected function getBasePathToStoreFiles()
    {
        return Regulation::FILE_PATH;
    }
}
