<?php


namespace common\models\attachment\attachmentCollection;

use common\components\AttachmentManager;
use common\components\attachmentSaveHandler\BaseAttachmentSaveHandler;
use common\components\attachmentSaveHandler\interfaces\AttachmentSaveHandlerInterface;
use common\models\Attachment;
use common\models\attachment\ActiveFormFileCollectionModel;
use common\models\attachment\BaseFileCollectionModel;
use common\models\AttachmentType;
use common\models\interfaces\AttachmentInterface;
use common\models\interfaces\FileToShowInterface;
use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\UploadedFile;

class BaseAttachmentCollection implements FileToShowInterface
{
    


    public $attachmentType;

    


    public $formName;

    


    private $index;

    


    private $attachmentSaveHandler;

    


    public $attachments = [];

    


    private $user;

    


    private $attachmentsErrors = [];

    






    public function __construct(AttachmentType $attachmentType, User $user, $attachments = [], $formName = null)
    {
        if ($attachments === null) {
            $attachments = [];
        }

        if (!is_array($attachments)) {
            $attachments = [$attachments];
        }

        $this->attachments = $attachments;
        $this->attachmentType = $attachmentType;
        $this->formName = $formName;

        $this->index = $attachmentType->id;

        $this->user = $user;

        $this->attachmentSaveHandler = new BaseAttachmentSaveHandler($this, $user);
    }

    


    public function isHidden(): ?bool
    {
        return ArrayHelper::getValue($this, 'attachmentType.hidden');
    }

    


    public function isRequired(): ?bool
    {
        return ArrayHelper::getValue($this, 'attachmentType.required');
    }

    public function getAttachmentTypeName(): ?string
    {
        return ArrayHelper::getValue($this, 'attachmentType.name');
    }

    public function getSendingProperties(): array
    {
        return ['attachment_type_id' => $this->attachmentType->id];
    }

    



    public function getModelEntity(): BaseFileCollectionModel
    {
        return new ActiveFormFileCollectionModel(!$this->isRequired(), Attachment::getExtensionsListForRules(), $this->getAttachmentType(), $this->formName);
    }

    public function getInitialPreviewConfig(): array
    {
        $config = [];
        foreach ($this->attachments as $attachment) {
            if (!$attachment->isNewRecord) {
                $fileName = "";
                if ($attachment->file instanceof UploadedFile) {
                    $fileName = $attachment->file->getBaseName();
                } else {
                    $fileName = ArrayHelper::getValue($attachment, 'linkedFile.upload_name', '');
                }
                $config[] = [
                    'caption' => (empty($attachment->id)) ? false : $fileName,
                    'type' => AttachmentManager::GetInitialPreviewConfigTypeByExtension($attachment->getExtension()),
                    'key' => $attachment->id
                ];
            }
        }
        return $config;
    }

    public function getFileDownloadUrl(): ?string
    {
        \Yii::$app->urlManager->suspendAddingReferrerParam(true);
        $url = Url::to(['site/download']);
        \Yii::$app->urlManager->suspendAddingReferrerParam(false);
        
        return $url;
    }
    
    public function getFileDeleteUrl(): ?string
    {
        return Url::to(['site/deletefile']);
    }

    public function getInitialPreviews(): array
    {
        $previews = [];
        foreach ($this->attachments as $attachment) {
            if (!$attachment->isNewRecord) {
                $previews[] = $attachment->getFileDownloadUrl();
            }
        }
        return $previews;
    }

    public function getAttachmentType(): ?AttachmentType
    {
        return $this->attachmentType;
    }

    


    public function setIndex(string $index): void
    {
        $this->index = $index;
    }

    


    public function getIndex(): string
    {
        return $this->index;
    }

    




    public function getInputName(): string
    {
        return "{$this->getModelEntity()->formName()}[file][{$this->getIndex()}]";
    }

    


    public function setAttachmentSaveHandler(AttachmentSaveHandlerInterface $attachmentSaveHandler): void
    {
        $this->attachmentSaveHandler = $attachmentSaveHandler;
    }

    


    public function getAttachmentSaveHandler(): AttachmentSaveHandlerInterface
    {
        return $this->attachmentSaveHandler;
    }

    


    public function getUser(): User
    {
        return $this->user;
    }

    


    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    


    public function addAttachmentsErrors(array $attachmentsErrors): void
    {
        $this->attachmentsErrors = array_merge($this->attachmentsErrors, $attachmentsErrors);
    }

    


    public function getAttachmentsErrors(): array
    {
        return $this->attachmentsErrors;
    }

    


    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_ATTACHMENT_COLLECTION;
    }
}