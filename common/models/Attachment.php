<?php

namespace common\models;

use backend\models\UploadableFileTrait;
use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\AttachmentManager;
use common\components\changeHistoryHandler\ApplicationActiveRecordChangeHistoryHandler;
use common\components\changeHistoryHandler\decorators\AttachmentChangeHistoryDecorator;
use common\components\changeHistoryHandler\interfaces\ChangeHistoryHandlerInterface;
use common\components\changeHistoryHandler\QuestionaryActiveRecordChangeHistoryHandler;
use common\components\FilesWorker\FilesWorker;
use common\components\ini\iniGet;
use common\models\interfaces\ArchiveModelInterface;
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\interfaces\FileToSendInterface;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\traits\ArchiveTrait;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\AbiturientQuestionary;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorPreferences;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\BachelorTargetReception;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\IndividualAchievement;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\ICanGetPathToStoreFile;
use common\modules\abiturient\models\interfaces\IHaveCallbackAfterDraftCopy;
use common\modules\abiturient\models\interfaces\OwnerConnectedInterface;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use common\modules\abiturient\models\PassportData;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;






























class Attachment extends ActiveRecord
implements
    ChangeLoggedModelInterface,
    FileToSendInterface,
    interfaces\AttachmentInterface,
    ApplicationConnectedInterface,
    QuestionaryConnectedInterface,
    OwnerConnectedInterface,
    ArchiveModelInterface,
    IHaveIdentityProp,
    ICanGetPathToStoreFile,
    ICanGivePropsToCompare,
    IHaveCallbackAfterDraftCopy

{
    use ArchiveTrait;
    use UploadableFileTrait;
    use HtmlPropsEncoder;

    public const SCENARIO_MARK_DELETED = 'mark_deleted';
    public const SCENARIO_RECOVER = 'recover';

    


    private $attachedEntity = null;

    


    private $_changeHistoryHandler;

    public $file;

    protected function setUpChangeHistoryHandler()
    {
        if ($this->_changeHistoryHandler === null && $this->attachmentType) {
            if ($this->attachmentType->inQuestionary()) {
                $this->setChangeHistoryHandler(new QuestionaryActiveRecordChangeHistoryHandler($this));
            } else {
                $this->setChangeHistoryHandler(new ApplicationActiveRecordChangeHistoryHandler($this));
            }
        }
    }

    public function afterFind()
    {
        $this->_initAttachedEntity();
        parent::afterFind();
    }

    public function _initAttachedEntity()
    {
        if (!$this->isCommon()) {
            switch ($this->attachmentType->system_type) {
                case AttachmentType::SYSTEM_TYPE_TARGET:
                    $this->attachedEntity = BachelorTargetReception::instance();
                    break;
                case AttachmentType::SYSTEM_TYPE_PREFERENCE:
                    $this->attachedEntity = BachelorPreferences::instance();
                    break;
                case AttachmentType::SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT:
                    $this->attachedEntity = IndividualAchievement::instance();
                    break;
                case AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY:
                    $this->attachedEntity = BachelorSpeciality::instance();
                    break;
                case AttachmentType::SYSTEM_TYPE_IDENTITY_DOCUMENT:
                    $this->attachedEntity = PassportData::instance();
                    break;
                case AttachmentType::SYSTEM_TYPE_EDUCATION_DOCUMENT:
                    $this->attachedEntity = EducationData::instance();
                    break;
                default:
                    $this->attachedEntity = null;
                    break;
            }
        }
    }

    public static function tableName()
    {
        return '{{%attachment}}';
    }

    public static function getFileRelationTable()
    {
        return '{{%attachments_files}}';
    }

    public static function getFileRelationColumn()
    {
        return 'attachment_id';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [
                [
                    'questionary_id',
                    'application_id',
                    'owner_id',
                    'archived_at'
                ],
                'integer'
            ],
            [
                [
                    'deleted',
                ],
                'boolean'
            ],
            [
                ['deleted'],
                'default',
                'value' => false
            ],
            [
                ['attachment_type_id'],
                'required',
                'except' => [self::SCENARIO_MARK_DELETED]
            ],
            [
                ['file'],
                'file',
                'extensions' => static::getExtensionsListForRules(),
                'skipOnEmpty' => true,
                'maxSize' => iniGet::getUploadMaxFilesize(false),
                'except' => [
                    self::SCENARIO_MARK_DELETED,
                    self::SCENARIO_RECOVER
                ]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'file' => Yii::t('abiturient/attachment', 'Подпись для поля "file" формы "Скан документа": `файл`'),
            'deleted' => Yii::t('abiturient/attachment', 'Подпись для поля "deleted" формы "Скан документа": `Удален`'),
            'filename' => Yii::t('abiturient/attachment', 'Подпись для поля "filename" формы "Скан документа": `Имя файла`'),
            'application_id' => Yii::t('abiturient/attachment', 'Подпись для поля "application_id" формы "Скан документа": `Id заявления`'),
            'questionary_id' => Yii::t('abiturient/attachment', 'Подпись для поля "questionary_id" формы "Скан документа": `Id Анкеты`'),
            'attachment_type_id' => Yii::t('abiturient/attachment', 'Подпись для поля "attachment_type_id" формы "Скан документа": `Тип прикрепляемого файла`'),
        ];
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_MARK_DELETED] = ['deleted'];
        $scenarios[self::SCENARIO_RECOVER] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
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

    public function getAttachmentLinkDependency()
    {
        return [
            BachelorSpeciality::class,
            BachelorPreferences::class,
            BachelorTargetReception::class,
            IndividualAchievement::class,
            UserRegulation::class,
            EducationData::class,
            PassportData::class,
        ];
    }

    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    public function getAttachmentType()
    {
        return $this->hasOne(AttachmentType::class, ['id' => 'attachment_type_id']);
    }

    public function getOwner()
    {
        return $this->hasOne(User::class, ['id' => 'owner_id']);
    }

    public function getOwnerId()
    {
        $abiturientQuestionary = $this->abiturientQuestionary;
        if (isset($abiturientQuestionary)) {
            return $abiturientQuestionary->user_id;
        }

        $bachelorApplication = $this->application;
        if (isset($bachelorApplication)) {
            return $bachelorApplication->user_id;
        }

        if (isset($this->owner_id)) {
            return $this->owner_id;
        }
        throw new ServerErrorHttpException('Не удалось определить владельца файла');
    }

    public function checkAccess($user)
    {
        if ($user->isModer() || $user->isViewer()) {
            return true;
        } elseif ($user->id == $this->getOwnerId()) {
            return true;
        } else {
            return false;
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        $disable_history = !$this->getChangeHistoryHandler() || $this->getChangeHistoryHandler()->getDisabled();
        if (!$disable_history && ArrayHelper::getValue($this, 'attachmentType.required')) {
            if ($this->application_id != null) {
                $application = BachelorApplication::findOne($this->application_id);
                if (isset($application)) {
                    if ($this->scenario != self::SCENARIO_MARK_DELETED) {
                        $application->resetStatus();
                    }
                }
            }
        }
    }

    public static function getNotFilledRequiredAttachmentTypeIds($attachments, $required_attachment_type_ids): array
    {
        if (empty($required_attachment_type_ids)) {
            return [];
        }
        $saved_attachment_type_ids = [];
        foreach ($attachments as $attachment) {
            if ($attachment->attachmentType && $attachment->attachmentType->need_one_of_documents) {
                $document_set_ref_id = $attachment->attachmentType->document_set_ref_id;
                if ($document_set_ref_id) {
                    $sibling_attachment_type_ids = AttachmentType::find()
                        ->where(['document_set_ref_id' => $document_set_ref_id])
                        ->andWhere(['not', ['id' => $attachment->attachment_type_id]])
                        ->select(['id'])
                        ->column();
                    $saved_attachment_type_ids = array_merge($saved_attachment_type_ids, $sibling_attachment_type_ids);
                }
            }
            $saved_attachment_type_ids[] = $attachment->attachment_type_id;
        }
        $saved_attachment_type_ids = array_values(array_unique($saved_attachment_type_ids));

        return array_diff($required_attachment_type_ids, $saved_attachment_type_ids);
    }

    public function getMimeType()
    {
        return AttachmentManager::GetMimeType($this->getExtension());
    }

    public function inRegulation(): bool
    {
        if ($this->isNewRecord) {
            return false;
        }
        return $this->userRegulation !== null;
    }

    public function getUserRegulation()
    {
        return $this->hasOne(UserRegulation::class, ['id' => 'user_regulation_id'])
            ->viaTable('{{%attachments-user_regulations}}', ['attachment_id' => 'id']);
    }

    public function beforeArchive()
    {
        $this->setScenario(Attachment::SCENARIO_MARK_DELETED);
    }

    


    public static function getArchiveColumn(): string
    {
        return 'deleted';
    }

    public static function getArchiveValue()
    {
        return true;
    }

    private function unlinkRegulation()
    {
        $regulation = $this->userRegulation;
        if ($regulation !== null) {
            $regulation->unlink('rawAttachments', $this, true);
        }
    }

    









    public function safeDelete(User $user, bool $updateHistory = true): bool
    {
        $transaction = Yii::$app->db->beginTransaction();
        if (!isset($transaction)) {
            throw new UserException('Ошибка создания транзакции');
        }

        $is_moder = Yii::$app->user->identity->isModer();

        try {
            $this->unlinkRegulation();

            
            $this->archive($updateHistory);

            if (
                !$is_moder &&
                $updateHistory &&
                $user->userRef &&
                $this->application_id != null
            ) {
                $application = BachelorApplication::findOne(['id' => $this->application_id]);

                if (
                    $application->resetStatus(false) &&
                    !$application->save()
                ) {
                    throw new UserException('Ошибка обновления статуса заявления.');
                }
            }

            if ($updateHistory) {
                $this->getChangeHistoryHandler()->getDeleteHistoryAction()->proceed();
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    










    public function silenceSafeDelete()
    {
        $transaction = Yii::$app->db->beginTransaction();
        if (!isset($transaction)) {
            throw new UserException('Ошибка создания транзакции');
        }
        try {
            $this->unlinkRegulation();

            
            $this->archive(false);

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
        return true;
    }

    public function getFileDownloadUrl(): ?string
    {
        return Url::to(['site/download', 'id' => $this->id]);
    }

    public function getFileDeleteUrl(bool $make_redirect = false): ?string
    {
        return Url::to(['site/deletefile', 'key' => $this->id, 'redirect_back' => $make_redirect]);
    }

    public static function getLinkableSystemTypes()
    {
        return [
            AttachmentType::SYSTEM_TYPE_PREFERENCE,
            AttachmentType::SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT,
            AttachmentType::SYSTEM_TYPE_TARGET,
            AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY,
            AttachmentType::SYSTEM_TYPE_IDENTITY_DOCUMENT,
            AttachmentType::SYSTEM_TYPE_EDUCATION_DOCUMENT,
        ];
    }

    


    public function isCommon(): bool
    {
        return $this->attachmentType->system_type === AttachmentType::SYSTEM_TYPE_COMMON;
    }

    


    public function isLinked(): bool
    {
        return in_array($this->attachmentType->system_type, Attachment::getLinkableSystemTypes());
    }

    



    public function getEntity(): ?ActiveQuery
    {
        $en = $this->attachedEntity;
        return $this->hasOne($en::getModel(), ['id' => $en::getEntityTableLinkAttribute()])->viaTable($en::getTableLink(), [
            $en::getAttachmentTableLinkAttribute() => 'id'
        ]);
    }

    public function getLinkedEntity(): ?AttachmentLinkableEntity
    {
        return $this->entity;
    }

    public function getAttachmentTypeName(): string
    {
        $name = null;
        if ($this->isLinked()) {
            try {
                $name = $this->entity->getName();
            } catch (\Throwable $e) {
                Yii::error("Не удалось невозможно получить связанную сущность: {$e->getMessage()}");
            }
        }
        return ($name ?? $this->attachmentType->name);
    }

    public function fileExists(): bool
    {
        return $this->linkedFile && $this->linkedFile->fileExists();
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'file',
            'attachment_type_id'
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_ATTACHMENT;
    }

    public function getOldAttribute($name)
    {
        return null;
    }

    public function getOldAttributes()
    {
        return null;
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_FILE;
    }

    


    public function setChangeHistoryHandler(ChangeHistoryHandlerInterface $handler): void
    {
        $this->_changeHistoryHandler = new AttachmentChangeHistoryDecorator($handler);
    }

    


    public function getChangeHistoryHandler(): ?ChangeHistoryHandlerInterface
    {
        $this->setUpChangeHistoryHandler();
        return $this->_changeHistoryHandler;
    }

    public function getOldClass(): ChangeLoggedModelInterface
    {
        return $this;
    }

    public function getEntityIdentifier(): ?string
    {
        $attachmentType = "";
        if (!($this->isCommon() || !$this->isLinked())) {
            try {
                $attachmentType = $this->getLinkedEntity()->getName();
            } catch (\Throwable $e) {
            }
        }
        if (!$attachmentType) {
            $attachmentType = ArrayHelper::getValue($this, 'attachmentType.name');
        }

        return $attachmentType;
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $errorFrom = '';
            $deleteSuccess = AttachmentManager::unlinkAttachmentFromAll($this);
            if (!$deleteSuccess) {
                $errorFrom .= "{$this->tableName()} -> {$this->id}\n";
            }

            if (!$deleteSuccess) {
                Yii::error("Ошибка при удалении данных с портала. В таблице: {$errorFrom}");
            }
            $this->deleteAttachedFile();

            return $deleteSuccess;
        } else {
            return false;
        }
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    public function getIdentityString(): string
    {
        $attachment_type_doc_uid = ArrayHelper::getValue($this, 'attachmentType.name');
        $attachment_type_name = ArrayHelper::getValue($this, 'attachmentType.documentType.ref_key');
        $file_name = $this->filename;
        return "{$attachment_type_doc_uid}_{$attachment_type_name}_{$file_name}";
    }

    public function getPropsToCompare(): array
    {
        return ArrayHelper::merge(
            array_diff(
                array_keys($this->attributes),
                [
                    'questionary_id',
                    'application_id',
                    'owner_id',
                    'attachment_type_id',
                ]
            ),
            [
                'filename',
            ]
        );
    }
}
