<?php

namespace backend\models;

use common\models\errors\RecordNotValid;
use common\models\interfaces\FileToSendInterface;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\modules\abiturient\models\File;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;






class MainPageInstructionFile
extends MainPageInstructionTemplate
implements FileToSendInterface
{
    use UploadableFileTrait;
    use HtmlPropsEncoder;

    public const ACCEPT_FILE_EXTENSIONS = 'txt';

    
    public $file = null;

    protected function getBasePathToStoreFiles()
    {
        return '@storage/web/main-page-instruction/';
    }

    


    public function rules()
    {
        return [
            [
                ['file'],
                'file',
                'skipOnEmpty' => true,
                'extensions' => static::ACCEPT_FILE_EXTENSIONS
            ],
            [
                'user_id',
                'default',
                'value' => null
            ],
            [
                'user_id',
                'integer'
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
        ];
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function attributeLabels()
    {
        return [
            'file' => Yii::t('backend', 'Файл'),
        ];
    }

    




    public function uploadFromPost(string $mainPageSettingId): void
    {
        $this->file = UploadedFile::getInstance($this, "[{$mainPageSettingId}]file");

        if (!$this->upload()) {
            throw new RecordNotValid($this);
        }
    }

    


    public function buildSourceUrl(): string
    {
        return Url::to([
            '/site/download-instruction-attachment',
            'id' => $this->main_page_setting_id
        ]);
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
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    


    public function getExtensions(): string
    {
        $linkedFile = $this->linkedFile;
        if (!$linkedFile) {
            return '';
        }
        $splitUploadName = explode('.', $linkedFile->upload_name);

        return end($splitUploadName);
    }

    






    public static function getInstructionData(
        array  $postData,
        string $instructionForm,
        string $mainPageSettingId
    ): array {
        return array_merge(
            parent::getInstructionData($postData, $instructionForm, $mainPageSettingId),
            ArrayHelper::getValue($_FILES, "{$instructionForm}.name.{$mainPageSettingId}", [])
        );
    }
}
