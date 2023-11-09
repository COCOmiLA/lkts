<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\AttachmentManager;
use common\components\queries\EnlistedApplicationQuery;
use common\components\ReferenceTypeManager\ContractorManager;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\components\validation_rules_providers\RulesProviderByDocumentType;
use common\models\Attachment;
use common\models\attachment\attachmentCollection\AttachedEntityAttachmentCollection;
use common\models\AttachmentType;
use common\models\dictionary\Contractor;
use common\models\dictionary\DocumentType;
use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use common\models\errors\RecordNotValid;
use common\models\interfaces\ArchiveModelInterface;
use common\models\interfaces\AttachmentLinkableApplicationEntity;
use common\models\interfaces\dynamic_validation_rules\IHavePropsRelatedToDocumentType;
use common\models\interfaces\FileToShowInterface;
use common\models\interfaces\IHaveDocumentCheckStatus;
use common\models\interfaces\IHaveIgnoredOnCopyingAttributes;
use common\models\relation_presenters\AttachmentsRelationPresenter;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\traits\ArchiveTrait;
use common\models\traits\DocumentCheckStatusTrait;
use common\models\traits\EnlistedApplicationQueryTrait;
use common\models\traits\FileAttachTrait;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\ICanAttachFile;
use common\modules\abiturient\models\interfaces\ICanBeStringified;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;































class BachelorTargetReception extends ChangeHistoryDecoratedModel
implements
    AttachmentLinkableApplicationEntity,
    ApplicationConnectedInterface,
    ArchiveModelInterface,
    IHasRelations,
    IHaveIdentityProp,
    ICanGivePropsToCompare,
    ICanAttachFile,
    IHaveIgnoredOnCopyingAttributes,
    IHavePropsRelatedToDocumentType,
    IHaveDocumentCheckStatus,
    ICanBeStringified
{
    use ArchiveTrait;
    use DocumentCheckStatusTrait;
    use EnlistedApplicationQueryTrait;
    use FileAttachTrait;
    use HasDirtyAttributesTrait;
    use HtmlPropsEncoder;

    public const NUMBER_OF_TARGET_RECEPTION = 3;

    public const SCENARIO_RECOVER = 'recover';

    public $tmp_uuid;

    public $not_found_document_contractor;

    public $not_found_target_contractor;

    protected bool $_new_record = true;

    protected ?RulesProviderByDocumentType $_document_type_validation_extender = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_document_type_validation_extender = new RulesProviderByDocumentType($this);
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->_new_record = false;
    }

    public function getIsActuallyNewRecord(): bool
    {
        return $this->_new_record;
    }

    public static function tableName()
    {
        return '{{%bachelor_target_reception}}';
    }

    


    public static function find()
    {
        return new EnlistedApplicationQuery(get_called_class());
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        $own_rules = [
            [
                [
                    'document_series',
                    'document_number',
                ],
                'trim'
            ],
            [
                [
                    'document_series',
                    'document_number',
                    'document_date',
                    'document_type'
                ],
                'string'
            ],
            [
                [
                    'archived_at',
                    'id_application',
                    'document_type_id',
                    'document_contractor_id',
                    'target_contractor_id',
                ],
                'integer'
            ],
            [
                [
                    'document_date',
                    'document_type_id',
                ],
                'required'
            ],
            [
                ['target_contractor_id'],
                'required',
                'whenClient' => "function(model, attribute) {
                    return false;
                }"
            ],
            [
                [
                    'from1c',
                    'archive',
                    'read_only',
                    'not_found_target_contractor',
                    'not_found_document_contractor',
                ],
                'boolean'
            ],
            [
                [
                    'archive',
                    'read_only',
                ],
                'default',
                'value' => false
            ],
            [
                'file',
                'safe',
                'on' => [self::SCENARIO_RECOVER]
            ],
            [
                ['document_type_id'], 'exist',
                'skipOnError' => false,
                'targetClass' => DocumentType::class,
                'targetAttribute' => ['document_type_id' => 'id']
            ],
            [
                ['document_check_status_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDocumentCheckStatusReferenceType::class,
                'targetAttribute' => ['document_check_status_ref_id' => 'id'],
            ],
        ];
        return [...$this->_document_type_validation_extender->getRules(), ...$own_rules];
    }

    public function ownRequiredRules(): array
    {
        return [
            [
                ['document_contractor_id', 'target_contractor_id'],
                'required',
                'whenClient' => "function(model, attribute) {
                    return !+$(attribute.input).attr('data-skip_validation');
                }"
            ],
            [
                [
                    'document_date',
                ],
                'required'
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_RECOVER] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    


    public function attributeLabels()
    {
        return [
            'file' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "file" формы "ЦП": `Копия документа`'),
            'filename' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "filename" формы "ЦП": `Имя файла`'),
            'document_date' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "document_date" формы "ЦП": `Дата выдачи`'),
            'document_type' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "document_type" формы "ЦП": `Тип документа`'),
            'id_application' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "id_application" формы "ЦП": `ID ПК`'),
            'document_number' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "document_number" формы "ЦП": `Номер`'),
            'document_series' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "document_series" формы "ЦП": `Серия`'),
            'document_type_id' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "document_type_id" формы "ЦП": `Тип документа`'),
            'document_contractor_id' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "document_contractor_id" формы "ЦП": `Кем выдано`'),
            'target_contractor_id' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "target_contractor_id" формы "ЦП": `Наименование организации`'),
            'documentTypeDescription' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "documentTypeDescription" формы "ЦП": `Тип документа`'),
            'attachments' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для файлов формы "ЦП": `Файл`'),
            'documentCheckStatus' => Yii::t('abiturient/bachelor/application/bachelor-target-reception', 'Подпись для поля "documentCheckStatus" формы "ЦП": `Статус проверки документа`'),
        ];
    }

    public function getDocumentType()
    {
        return $this->hasOne(
            DocumentType::class,
            ['id' => 'document_type_id']
        );
    }

    






    public function checkAccess($user)
    {
        $application = $this->application;
        if ($user->isModer()) {
            return true;
        }

        if (isset($application) && $user->id == $application->user_id) {
            return true;
        }

        return false;
    }

    




    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'id_application']);
    }

    public static function getTableLink(): string
    {
        return 'target_reception_attachment';
    }

    public static function getEntityTableLinkAttribute(): string
    {
        return 'target_reception_id';
    }

    public static function getAttachmentTableLinkAttribute(): string
    {
        return 'attachment_id';
    }

    public function getAttachments(): ActiveQuery
    {
        return $this->getRawAttachments()
            ->andOnCondition([
                Attachment::tableName() . '.deleted' => false
            ]);
    }

    public function getRawAttachments(): ActiveQuery
    {
        return $this->hasMany(Attachment::class, ['id' => self::getAttachmentTableLinkAttribute()])
            ->viaTable(self::getTableLink(), [
                self::getEntityTableLinkAttribute() => 'id'
            ]);
    }

    




    public function getAttachmentCollection(): FileToShowInterface
    {
        return new AttachedEntityAttachmentCollection(
            ArrayHelper::getValue($this, 'application.user'),
            $this,
            AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_TARGET),
            $this->attachments,
            $this->formName(),
            'file'
        );
    }

    public static function getModel(): string
    {
        return get_called_class();
    }

    public static function getDbTableSchema(): TableSchema
    {
        return parent::getTableSchema();
    }

    public function getAttachmentType(): ?AttachmentType
    {
        return AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_TARGET);
    }

    public function getName(): string
    {
        return Yii::t(
            'abiturient/bachelor/application/bachelor-target-reception',
            'Наименование текущего ЦП: `Целевой договор {nameCompany} (Серия {documentSeries} № {documentNumber})`',
            [
                'nameCompany' => $this->targetContractor->name ?? '',
                'documentSeries' => $this->document_series,
                'documentNumber' => $this->document_number,
            ]
        );
    }

    public function stringify(): string
    {
        return $this->getName();
    }

    public static function getApplicationIdColumn(): string
    {
        return 'id_application';
    }

    public function getAttachmentConnectors(): array
    {
        return ['application_id' => $this->application->id];
    }

    public function getUserInstance(): User
    {
        return ArrayHelper::getValue($this, 'application.user') ?: new User();
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'document_series',
            'document_number',
            'document_date',
            'document_type' => function ($model) {
                return $model->documentType === null ? null : $model->documentType->description;
            },
            'document_contractor_id' => function ($model) {
                return $model->documentContractor->name ?? '';
            },
            'target_contractor_id' => function ($model) {
                return $model->targetContractor->name ?? '';
            }
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_TARGET_RECEPTION;
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            AttachmentManager::unlinkAllAttachment($this);
            $this->beforeArchive();
            return true;
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

    public function getRawBachelorSpecialities()
    {
        return $this->hasMany(BachelorSpeciality::class, ['target_reception_id' => 'id']);
    }

    public function getBachelorSpecialities()
    {
        return $this->getRawBachelorSpecialities()->active();
    }

    public function beforeArchive()
    {
        foreach ($this->getRawBachelorSpecialities()->all() as $speciality) {
            $speciality->target_reception_id = null;
            $speciality->save(false);
        }
    }

    
















    public static function GetOrCreateFromRaw(
        array               $target_organization,
        string              $document_series,
        string              $document_number,
        array               $document_organization,
        string              $document_date,
                            $raw_document_type_ref,
        array               $documentCheckStatusRef = [],
        bool                $documentReadOnly = false,
        BachelorApplication $application,
        array               $ids_to_ignore = []
    ): BachelorTargetReception {
        $target_contractor = ContractorManager::GetOrCreateContractor($target_organization);
        $document_contractor = ContractorManager::GetOrCreateContractor($document_organization);
        $target = $application->getBachelorTargetReceptions()
            ->joinWith(['documentType dt'])
            ->andFilterWhere(['not', [BachelorTargetReception::tableName() . '.id' => $ids_to_ignore]])
            ->andWhere([
                BachelorTargetReception::tableName() . '.target_contractor_id' => $target_contractor->id ?? null,
                BachelorTargetReception::tableName() . '.document_series' => $document_series,
                BachelorTargetReception::tableName() . '.document_number' => $document_number,
                BachelorTargetReception::tableName() . '.document_contractor_id' => $document_contractor->id ?? null,
            ])
            ->andFilterWhere([
                'dt.ref_key' => ReferenceTypeManager::GetOrCreateReference(
                    DocumentType::class,
                    $raw_document_type_ref
                )->ref_key ?? null
            ])
            ->one();
        if (!$target) {
            $target = new BachelorTargetReception();
            $target->id_application = $application->id;
        }

        $target->read_only = $documentReadOnly;
        $target->setDocumentCheckStatusFrom1CData($documentCheckStatusRef);

        $target->target_contractor_id = $target_contractor->id;
        $target->document_series = $document_series;
        $target->document_number = $document_number;
        $target->document_contractor_id = $document_contractor->id;
        $target->document_date = date('d.m.Y', strtotime($document_date));
        $docRef = ReferenceTypeManager::GetOrCreateReference(DocumentType::class, $raw_document_type_ref);
        $target->document_type_id = $docRef->id ?? null;
        $target->document_type = $docRef->code ?? null;
        $target->from1c = true;
        $target->archive = false;

        if (!$target->save(false)) {
            throw new RecordNotValid($target);
        }

        return $target;
    }

    public function getRelationsInfo(): array
    {
        return [
            new AttachmentsRelationPresenter('attachments', [
                'parent_instance' => $this,
            ]),
        ];
    }

    public function getDocumentTypeDescription()
    {
        return ArrayHelper::getValue($this, 'documentType.description');
    }

    public function getIdentityString(): string
    {
        $document_uid = ArrayHelper::getValue($this, 'documentType.ref_key', '');
        return "{$document_uid}_{$this->document_series}_{$this->document_number}";
    }

    public function getPropsToCompare(): array
    {
        return ArrayHelper::merge(
            array_diff(
                array_keys($this->attributes),
                [
                    'id_application'
                ]
            ),
            [
                'documentTypeDescription',
            ]
        );
    }

    public function getAttachedFilesInfo(): array
    {
        $files = [];
        foreach ($this->attachments as $attachment) {
            $files[] = [
                $attachment,
                ArrayHelper::getValue($this, 'documentType'),
                null
            ];
        }
        return $files;
    }

    public function getIgnoredOnCopyingAttributes(): array
    {
        return [
            ...DraftsManager::$attributes_to_ignore,
            'id_application'
        ];
    }

    


    public function hasEnlistedBachelorSpecialities(): bool
    {
        $tn = BachelorSpeciality::tableName();
        return $this->getBachelorSpecialities()
            ->andWhere(["{$tn}.is_enlisted" => true])
            ->exists();
    }

    public function getDocumentContractor(): ActiveQuery
    {
        return $this->hasOne(Contractor::class, ['id' => 'document_contractor_id']);
    }

    public function getTargetContractor(): ActiveQuery
    {
        return $this->hasOne(Contractor::class, ['id' => 'target_contractor_id']);
    }

    public static function getDocumentTypePropertyName(): string
    {
        return 'documentType';
    }

    public static function getSubdivisionCodePropertyName(): string
    {
        return '';
    }

    public static function getIssuedDatePropertyName(): string
    {
        return 'document_date';
    }

    public static function getDateOfEndPropertyName(): string
    {
        return '';
    }

    public static function getAdditionalPropertyName(): string
    {
        return '';
    }

    public static function getIssuedByPropertyName(): string
    {
        return 'document_contractor_id';
    }

    public static function getDocumentSeriesPropertyName(): string
    {
        return 'document_series';
    }

    public static function getDocumentNumberPropertyName(): string
    {
        return 'document_number';
    }

    




    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if (!$this->fillDocumentCheckStatusIfNotVerified()) {
            return false;
        }

        return true;
    }
}
