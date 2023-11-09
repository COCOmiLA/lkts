<?php

namespace common\modules\abiturient\models\chat;

use backend\models\UploadableFileTrait;
use common\components\AttachmentManager;
use common\components\behaviors\timestampBehavior\TimestampBehaviorMilliseconds;
use common\components\DateTimeHelper;
use common\components\FilesWorker\FilesWorker;
use common\components\ini\iniGet;
use common\components\UUIDManager;
use common\models\errors\RecordNotValid;
use common\models\interfaces\FileToSendInterface;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\modules\abiturient\models\File;
use Yii;
use yii\base\Controller;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\UploadedFile;






















class ChatFileBase extends ActiveRecord implements FileToSendInterface
{
    use UploadableFileTrait;
    use HtmlPropsEncoder;

    public const STATUS_NEW = 1;
    public const STATUS_READ = 2;

    
    public $file = null;

    


    public static function tableName()
    {
        return '{{%chat_file}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehaviorMilliseconds::class];
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        foreach ($this->getChatHistory()->all() as $dataToDelete) {
            if (!$dataToDelete->delete()) {
                $errorFrom = "{$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");

                return false;
            }
        }

        return true;
    }

    


    public function rules()
    {
        return [
            [
                [
                    'chat_id',
                    'author_id',
                ],
                'required'
            ],
            [
                [
                    'status',
                    'chat_id',
                    'author_id',
                    'created_at',
                    'updated_at',
                ],
                'integer'
            ],
            [
                ['status'],
                'default',
                'value' => ChatFileBase::STATUS_NEW
            ],
            [
                ['mark_is_not_read',],
                'boolean'
            ],
            [
                ['chat_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ChatBase::class,
                'targetAttribute' => ['chat_id' => 'id']
            ],
            [
                ['author_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => ChatUserBase::class,
                'targetAttribute' => ['author_id' => 'id']
            ],
            [
                ['file_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => File::class,
                'targetAttribute' => ['file_id' => 'id']
            ],
            [
                ['file'],
                'file',
                'extensions' => static::getExtensionsListForRules(),
                'skipOnEmpty' => false,
                'maxSize' => iniGet::getUploadMaxFilesize(false),
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [];
    }

    




    public function getChatHistory(): ActiveQuery
    {
        return $this->hasOne(ChatHistoryBase::class, ['file_id' => 'id']);
    }

    




    public function getFile(): ActiveQuery
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    




    public function getChat(): ActiveQuery
    {
        return $this->hasOne(ChatBase::class, ['id' => 'chat_id']);
    }

    




    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(ChatUserBase::class, ['id' => 'author_id']);
    }

    








    public static function createNewFile(ChatBase $chat, array $files, ChatUserBase $user): void
    {
        foreach ($files as $key => $file) {
            $chatFile = new ChatFileBase();

            $chatFile->file = UploadedFile::getInstanceByName("file[{$key}]");
            $chatFile->chat_id = $chat->id;
            $chatFile->author_id = $user->id;

            if (!$chatFile->upload()) {
                throw new RecordNotValid($chatFile);
            }

            ChatHistoryBase::addedNewFile($chatFile);
        }
    }

    







    public function render($controller, User $user): string
    {
        $file = $this->linkedFile;
        if (!$file) {
            return '';
        }

        $data = [
            'time' => DateTimeHelper::dateFromMstime('d.m.Y H:i', $this->created_at),
            'nickname' => $this->author->nickname,
            'fileUid' => UUIDManager::GetUUID(),
            'fileName' => $file->upload_name,
            'fileDownloadUrl' => $this->fileDownloadUrl,
        ];

        $path = '@chatPartialView/outgoing-file-template';
        if ($this->author->user_id == $user->id) {
            $path = '@chatPartialView/incoming-file-template';
            $data['status'] = 'fa fa-check success';
        }


        return $controller->renderPartial($path, $data);
    }

    




    public function renderForNotification(): string
    {
        return Yii::t(
            'abiturient/chat/chat-file',
            'Текстовка для сообщения в системе уведомлений, о том что в чат был добавлен файл: `Прикреплён файл. Просмотрите в чате.`'
        );
    }

    


    public function getLinkedFile(): ActiveQuery
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }

    


    protected function getOwnerId(): ?int
    {
        return $this->author_id;
    }

    public function getFileDownloadUrl(): ?string
    {
        return Url::to(['chat/download', 'id' => $this->id]);
    }

    




    public function checkAccess(User $user): bool
    {
        
        return true;
    }

    


    public function getMimeType(): string
    {
        return AttachmentManager::GetMimeType($this->getExtension());
    }

    






    public static function getTotalFilesCountQuery(ChatBase $chat): ActiveQuery
    {
        $tn = static::tableName();
        return static::find()
            ->andWhere(["{$tn}.chat_id" => $chat->id]);
    }

    






    public static function getTotalFilesCount(ChatBase $chat): int
    {
        return static::getTotalFilesCountQuery($chat)->count();
    }

    








    public static function getFilesCountByUserQuery(ChatBase $chat, ChatUserBase $user): ActiveQuery
    {
        $tn = static::tableName();
        return static::getTotalFilesCountQuery($chat)
            ->andWhere(["{$tn}.author_id" => $user->id]);
    }

    








    public static function getNotReadFilesCountByUserQuery(ChatBase $chat, ChatUserBase $user): ActiveQuery
    {
        $tn = static::tableName();
        return static::getFilesCountByUserQuery($chat, $user)
            ->andWhere(["{$tn}.status" => static::STATUS_NEW]);
    }

    








    public static function getNotReadFilesCountByUser(ChatBase $chat, ChatUserBase $user): int
    {
        return static::getNotReadFilesCountByUserQuery($chat, $user)->count();
    }

    




    public static function getExtensionsListForRules(): string
    {
        return implode(', ', static::getExtensionsList());
    }

    




    public static function getExtensionsListForJs(): string
    {
        $stringList = implode('|\.', static::getExtensionsList());
        return "(\.{$stringList})$";
    }

    




    public static function getExtensionsList(): array
    {
        return FilesWorker::getAllowedExtensionsToUploadList();
    }
}
