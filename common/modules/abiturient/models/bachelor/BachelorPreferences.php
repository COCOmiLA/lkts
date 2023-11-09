<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\AttachmentManager;
use common\components\queries\EnlistedApplicationQuery;
use common\components\queries\EnlistedApplicationQueryForBachelorPreferences;
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
use common\models\dictionary\Olympiad;
use common\models\dictionary\Privilege;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
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
use stdClass;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;





































class BachelorPreferences extends ChangeHistoryDecoratedModel
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

    protected bool $_new_record = true;
    protected ?RulesProviderByDocumentType $_document_type_validation_extender = null;

    public $tmp_uuid;

    public const SCENARIO_RECOVER = 'recover';
    public const SCENARIO_ARCHIVE = 'archive';

    public const TYPE_OLYMP = 'olymp';
    public const TYPE_PREF = 'pref';

    public $notFoundContractor;

    




    private $preferenceType;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_document_type_validation_extender = new RulesProviderByDocumentType($this);
    }

    public static function tableName()
    {
        return '{{%bachelor_preferences}}';
    }

    


    public static function find()
    {
        return new EnlistedApplicationQueryForBachelorPreferences(get_called_class());
    }

    


    public function setPreferenceType(string $preferenceType): void
    {
        $this->preferenceType = $preferenceType;
        $this->scenario = $preferenceType;
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
                    'code',
                    'document_series',
                    'document_number',
                    'olympiad_code',
                    'document_date',
                    'privilege_code',
                    'special_mark_code',
                    'document_type',
                ],
                'string'
            ],
            [
                [
                    'from1c',
                    'archive',
                    'read_only',
                    'priority_right',
                    'individual_value',
                    'notFoundContractor',
                ],
                'boolean'
            ],
            [
                [
                    'archived_at',
                    'id_application',
                    'privilege_id',
                    'special_mark_id',
                    'document_type_id',
                    'olympiad_id',
                    'contractor_id',
                ],
                'integer'
            ],
            [
                'document_type_id',
                'required'
            ],
            [
                ['code'],
                'required',
                'on' => [self::TYPE_PREF]
            ],
            [
                'olympiad_id',
                'required',
                'on' => [self::TYPE_OLYMP]
            ],
            [
                ['privilege_id'],
                'required',
                'when' => function (BachelorPreferences $model) {
                    return !$model->special_mark_id;
                },
                'whenClient' => "function(attribute, value) { return false; }",
                'on' => [self::TYPE_PREF]
            ],
            [
                ['special_mark_id'],
                'required',
                'when' => function (BachelorPreferences $model) {
                    return !$model->privilege_id;
                },
                'whenClient' => "function(attribute, value) { return false; }",
                'on' => [self::TYPE_PREF]
            ],
            [
                [
                    'read_only',
                    'archive',
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
                ['privilege_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => Privilege::class,
                'targetAttribute' => ['privilege_id' => 'id']
            ],
            [
                ['special_mark_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => SpecialMark::class,
                'targetAttribute' => ['special_mark_id' => 'id']
            ],
            [
                ['document_type_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => DocumentType::class,
                'targetAttribute' => ['document_type_id' => 'id']
            ],
            [
                ['olympiad_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => Olympiad::class,
                'targetAttribute' => ['olympiad_id' => 'id']
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
                ['contractor_id'],
                'required',
                'whenClient' => "function(model, attribute) {
                    return !+$(attribute.input).attr('data-skip_validation');
                }"
            ],
            [
                [
                    'document_series',
                    'document_number',
                    'document_date',
                ],
                'required'
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'code' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "code" формы "льгот-БВИ": `Льготы доступные для выбора`'),
            'file' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "file" формы "льгот-БВИ": `Копия документа`'),
            'year' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "year" формы "льгот-БВИ": `Год`'),
            'olympiadYear' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "olympiadYear" формы "льгот-БВИ": `Год`'),
            'filename' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "filename" формы "льгот-БВИ": `Имя файла`'),
            'description' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "description" формы "льгот-БВИ": `Описание`'),
            'olympiad_id' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "olympiad_id" формы "льгот-БВИ": `Олимпиада`'),
            'privilege_id' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "privilege_id" формы "льгот-БВИ": `Тип льготы`'),
            'document_date' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "document_date" формы "льгот-БВИ": `Дата выдачи`'),
            'document_type' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "document_type" формы "льгот-БВИ": `Тип документа`'),
            'olympiad_code' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "olympiad_code" формы "льгот-БВИ": `Олимпиада`'),
            'olympiadName' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "olympiadName" формы "льгот-БВИ": `Наименование олимпиады`'),
            'olympiadClass' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "olympiadClass" формы "льгот-БВИ": `Класс`'),
            'id_application' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "id_application" формы "льгот-БВИ": `ID ПК`'),
            'priority_right' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "priority_right" формы "льгот-БВИ": `Преимущественное право (поступаю на общих основаниях)`'),
            'privilege_code' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "privilege_code" формы "льгот-БВИ": `Тип льготы`'),
            'document_number' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "document_number" формы "льгот-БВИ": `Номер`'),
            'document_series' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "document_series" формы "льгот-БВИ": `Серия`'),
            'special_mark_id' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "special_mark_id" формы "льгот-БВИ": `Тип льготы`'),
            'document_type_id' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "document_type_id" формы "льгот-БВИ": `Тип документа`'),
            'individual_value' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "individual_value" формы "льгот-БВИ": `Льгота`'),
            'special_mark_code' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "special_mark_code" формы "льгот-БВИ": `Тип льготы`'),
            'benefitDescription' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "benefitDescription" формы "льгот-БВИ": `Описание льготы`'),
            'contractor_id' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "contractor_id" формы "льгот-БВИ": `Кем выдано`'),
            'documentTypeDescription' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "documentTypeDescription" формы "льгот-БВИ": `Тип документа`'),
            'specialMarkDescription' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "specialMarkDescription" формы "льгот-БВИ": `Особая отметка`'),
            'humanized_priority_right' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "humanized_priority_right" формы "льгот-БВИ": `Преимущественное право (поступаю на общих основаниях)`'),
            'humanized_individual_value' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "humanized_individual_value" формы "льгот-БВИ": `Льгота`'),
            'benefitSign' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "benefitSign" формы "льгот-БВИ": `Отличительный признак`'),
            'attachments' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для файлов формы "льгот-БВИ": `Файл`'),
            'documentCheckStatus' => Yii::t('abiturient/bachelor/application/bachelor-preferences', 'Подпись для поля "documentCheckStatus" формы "льгот-БВИ": `Статус проверки документа`'),
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_RECOVER] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_ARCHIVE] = ['archive'];
        return $scenarios;
    }

    




    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'id_application']);
    }

    




    public function getDescription()
    {
        $result = '';
        if ($this->olympiad) {
            $ol = $this->olympiad;
            $result = $ol->name ?? '';
        }
        if ($this->privilege) {
            $priv = $this->privilege;
            $result = $priv->description ?? '';
        }
        if ($this->specialMark) {
            $spec = $this->specialMark;
            $result = $spec->description ?? '';
        }
        return $result;
    }

    public function getDocumentType()
    {
        return $this->hasOne(
            DocumentType::class,
            ['id' => 'document_type_id']
        );
    }

    public function isOlymp(): bool
    {
        return !empty($this->olympiad);
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

    public function getAttachmentTypeName(): ?string
    {
        $res = "{$this->getBenefitSign()}: ";
        $res .= $this->getName();
        return $res;
    }


    public static function getTableLink(): string
    {
        return 'preference_attachment';
    }

    public static function getEntityTableLinkAttribute(): string
    {
        return 'preference_id';
    }

    public static function getAttachmentTableLinkAttribute(): string
    {
        return 'attachment_id';
    }

    public function getRawBachelorSpecialities()
    {
        return $this->hasMany(BachelorSpeciality::class, ['preference_id' => 'id']);
    }

    public function getBachelorSpecialities()
    {
        return $this->getRawBachelorSpecialities()->active();
    }

    public function getRawBachelorSpecialitiesWithOlympiad()
    {
        return $this->hasMany(BachelorSpeciality::class, ['bachelor_olympiad_id' => 'id']);
    }

    public function getBachelorSpecialitiesWithOlympiad()
    {
        return $this->getRawBachelorSpecialitiesWithOlympiad()->active();
    }

    public function beforeArchive()
    {
        $this->setScenario(self::SCENARIO_ARCHIVE);

        foreach ($this->getRawBachelorSpecialities()->all() as $speciality) {
            $speciality->preference_id = null;
            $speciality->save(false);
        }
    }

    public function getAttachments(): ActiveQuery
    {
        return $this->getRawAttachments()
            ->andOnCondition([Attachment::tableName() . '.deleted' => false]);
    }

    public function getRawAttachments(): ActiveQuery
    {
        return $this->hasMany(Attachment::class, ['id' => self::getAttachmentTableLinkAttribute()])
            ->viaTable(self::getTableLink(), [self::getEntityTableLinkAttribute() => 'id']);
    }

    



    public function getAttachmentCollection(): FileToShowInterface
    {
        $customIndex = $this->preferenceType === self::TYPE_OLYMP ? 'olymp' : 'pref';
        return new AttachedEntityAttachmentCollection(
            ArrayHelper::getValue($this, 'application.user'),
            $this,
            AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_PREFERENCE),
            $this->attachments,
            $this->formName(),
            'file',
            $customIndex
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
        return AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_PREFERENCE);
    }

    public function getName(): string
    {
        return "{$this->getDescription()} (Серия {$this->document_series} № {$this->document_number})";
    }

    public function stringify(): string
    {
        return $this->getName();
    }

    public static function getApplicationIdColumn(): string
    {
        return 'id_application';
    }

    public function getOlympiad()
    {
        return $this->hasOne(Olympiad::class, ['id' => 'olympiad_id']);
    }

    public function getSpecialMark()
    {
        return $this->hasOne(SpecialMark::class, ['id' => 'special_mark_id']);
    }

    public function getPrivilege()
    {
        return $this->hasOne(Privilege::class, ['id' => 'privilege_id']);
    }

    public function getAttachmentConnectors(): array
    {
        return ['application_id' => $this->application->id];
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->_new_record = false;
    }

    public function getUserInstance(): User
    {
        return ArrayHelper::getValue($this, 'application.user') ?: new User();
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'olympiad_id' => function ($model) {
                return ArrayHelper::getValue($model, 'olympiad.name');
            },
            'document_series',
            'document_number',
            'contractor_id' => function ($model) {
                return $model->contractor->name ?? '';
            },
            'document_date',
            'document_type',
            'priority_right',
            'privilege_id' => function ($model) {
                return ArrayHelper::getValue($model, 'privilege.description');
            },
            'special_mark_id' => function ($model) {
                return ArrayHelper::getValue($model, 'specialMark.description');
            },
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_BACHELOR_PREFERENCES;
    }

    public function beforeValidate()
    {
        $this->special_mark_code = ArrayHelper::getValue($this, 'specialMark.code');
        $this->olympiad_code = ArrayHelper::getValue($this, 'olympiad.olympicRef.reference_id');
        $this->privilege_code = ArrayHelper::getValue($this, 'privilege.code');
        $this->document_type = ArrayHelper::getValue($this, 'documentType.code');

        return parent::beforeValidate();
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

    


















    public static function GetOrCreateFromRaw(
        string              $document_series,
        string              $document_number,
        array               $document_organization,
        string              $document_date,
        $raw_privilege_ref,
        $raw_olympic_ref,
        $raw_special_mark_ref,
        $raw_document_type_ref,
        array               $documentCheckStatusRef = [],
        bool                $documentReadOnly = false,
        BachelorApplication $application,
        array               $ids_to_ignore = []
    ): BachelorPreferences {
        $contractor = ContractorManager::GetOrCreateContractor($document_organization);
        $benefit = $application->getPreferences()
            ->joinWith([
                'privilege p',
                'specialMark sp',
                'olympiad.olympicRef or',
                'documentType dt'
            ])
            ->andFilterWhere(['not', [BachelorPreferences::tableName() . '.id' => $ids_to_ignore]])
            ->andWhere([
                BachelorPreferences::tableName() . '.document_series' => $document_series,
                BachelorPreferences::tableName() . '.document_number' => $document_number,
                BachelorPreferences::tableName() . '.contractor_id' => $contractor->id ?? null,
            ])
            ->andFilterWhere([
                'p.ref_key' => ReferenceTypeManager::GetOrCreateReference(
                    Privilege::class,
                    $raw_privilege_ref
                )->ref_key ?? null
            ])
            ->andFilterWhere([
                'or.reference_uid' => ArrayHelper::getValue(
                    ReferenceTypeManager::GetOrCreateReference(
                        Olympiad::class,
                        $raw_olympic_ref
                    ),
                    'olympicRef.reference_uid'
                )
            ])
            ->andFilterWhere([
                'sp.ref_key' => ReferenceTypeManager::GetOrCreateReference(
                    SpecialMark::class,
                    $raw_special_mark_ref
                )->ref_key ?? null
            ])
            ->andFilterWhere([
                'dt.ref_key' => ReferenceTypeManager::GetOrCreateReference(
                    DocumentType::class,
                    $raw_document_type_ref
                )->ref_key ?? null
            ])
            ->one();

        if (!$benefit) {
            $benefit = new BachelorPreferences();
            $benefit->id_application = $application->id;
        }

        $benefit->read_only = $documentReadOnly;
        $benefit->setDocumentCheckStatusFrom1CData($documentCheckStatusRef);

        $benefit->from1c = true;
        $benefit->document_series = $document_series;
        $benefit->document_number = $document_number;
        $benefit->contractor_id = $contractor->id;
        $benefit->document_date = date('d.m.Y', strtotime($document_date));

        $olympRef = ReferenceTypeManager::GetOrCreateReference(Olympiad::class, $raw_olympic_ref);
        $benefit->olympiad_id = $olympRef->id ?? null;

        $benefit->privilege_id = ReferenceTypeManager::GetOrCreateReference(Privilege::class, $raw_privilege_ref)->id ?? null;
        $benefit->special_mark_id = ReferenceTypeManager::GetOrCreateReference(SpecialMark::class, $raw_special_mark_ref)->id ?? null;
        $docType = ReferenceTypeManager::GetOrCreateReference(DocumentType::class, $raw_document_type_ref);
        $benefit->document_type_id = $docType->id ?? null;
        $benefit->document_type = $docType->code ?? null;

        
        $benefit->privilege_code = null;
        $benefit->special_mark_code = null;
        $benefit->olympiad_code = null;
        $benefit->archive = false;

        if (!$benefit->save(false)) {
            throw new RecordNotValid($benefit);
        }

        return $benefit;
    }

    public function getRelationsInfo(): array
    {
        return [
            new AttachmentsRelationPresenter('attachments', [
                'parent_instance' => $this,
            ]),
        ];
    }

    public function getIdentityString(): string
    {
        $privilege_uid = ArrayHelper::getValue($this, 'privilege.ref_key', '');
        $spec_mark_uid = ArrayHelper::getValue($this, 'specialMark.ref_key', '');
        $olymp_uid = ArrayHelper::getValue($this, 'olympiad.olympicRef.reference_uid', '');
        $document_uid = ArrayHelper::getValue($this, 'documentType.ref_key', '');
        return "{$privilege_uid}_{$spec_mark_uid}_{$olymp_uid}_{$document_uid}_{$this->document_series}_{$this->document_number}";
    }

    public function getOlympiadName()
    {
        return ArrayHelper::getValue($this, 'olympiad.name');
    }

    public function getDocumentTypeDescription()
    {
        return ArrayHelper::getValue($this, 'documentType.description');
    }

    public function getOlympiadYear()
    {
        return ArrayHelper::getValue($this, 'olympiad.year');
    }

    public function getOlympiadClass()
    {
        return ArrayHelper::getValue($this, 'olympiad.class');
    }

    public function getBenefitDescription()
    {
        $lgota = $this->privilege;
        if (!$lgota) {
            $lgota = $this->specialMark;
        }
        return ArrayHelper::getValue($lgota, 'description');
    }

    public function getSpecialMarkDescription()
    {
        return ArrayHelper::getValue($this, 'specialMark.description');
    }

    public function getBenefitSign()
    {
        if ($this->isOlymp()) {
            return Yii::t(
                'abiturient/bachelor/application/bachelor-preferences',
                'Подпись для олимпиады: `Олимпиада`'
            );
        }

        if ($this->priority_right && !$this->individual_value) {
            return Yii::t(
                'abiturient/bachelor/application/bachelor-preferences',
                'Подпись для преимущественного права: `Преимущественное право`'
            );
        } elseif ($this->priority_right && $this->individual_value) {
            return Yii::t(
                'abiturient/bachelor/application/bachelor-preferences',
                'Подпись для преимущественного права: `Преимущественное право`'
            ) . ' / ' . Yii::t(
                'abiturient/bachelor/application/bachelor-preferences',
                'Подпись для льготы: `Льгота`'
            );
        } elseif (!$this->priority_right && $this->individual_value) {
            return Yii::t(
                'abiturient/bachelor/application/bachelor-preferences',
                'Подпись для льготы: `Льгота`'
            );
        }
        return '';
    }

    public function getHumanized_priority_right()
    {
        return $this->priority_right ? Yii::t(
            'abiturient/bachelor/application/bachelor-preferences',
            'Подпись наличия флага особого права таблицы льгот; в блоке льгот на стр. просмотра заявления: `Да`'
        ) : Yii::t(
            'abiturient/bachelor/application/bachelor-preferences',
            'Подпись отсутствия флага особого права таблицы льгот; в блоке льгот на стр. просмотра заявления: `Нет`'
        );
    }

    public function getHumanized_individual_value()
    {
        return $this->individual_value ? Yii::t(
            'abiturient/bachelor/application/bachelor-preferences',
            'Подпись наличия флага особого права таблицы льгот; в блоке льгот на стр. просмотра заявления: `Да`'
        ) : Yii::t(
            'abiturient/bachelor/application/bachelor-preferences',
            'Подпись отсутствия флага особого права таблицы льгот; в блоке льгот на стр. просмотра заявления: `Нет`'
        );
    }

    public function getPropsToCompare(): array
    {
        return ArrayHelper::merge(
            array_diff(
                array_keys($this->attributes),
                [
                    'id_application',
                    'olympiad_code',
                    'privilege_code',
                    'special_mark_code',
                    'document_type',
                    'from1c',
                    'file',
                    'code',
                    'size',
                ]
            ),
            [
                'olympiadName',
                'olympiadYear',
                'olympiadClass',
                'benefitSign',
                'documentTypeDescription',
                'benefitDescription',
                'specialMarkDescription',
                'description',
                'humanized_priority_right',
                'humanized_individual_value',
            ]
        );
    }

    public function getIsActuallyNewRecord(): bool
    {
        return $this->_new_record;
    }

    



    public static function getBenefitByHashKey(string $hash_key): ActiveRecord
    {
        $reference = null;
        $params = explode('_', $hash_key);

        if ($params[1] == SpecialMark::KEY) {
            $reference = SpecialMark::findByUID($params[0]);
        } else {
            $reference = Privilege::findByUID($params[0]);
        }
        return $reference;
    }

    public function getCode(): ?string
    {
        $benefit = $this->privilege;
        if (!$benefit) {
            $benefit = $this->specialMark;
        }
        
        return ArrayHelper::getValue($benefit, 'hashCode');
    }

    public function setCode(string $hash_code)
    {
        $params = explode('_', $hash_code);

        $reference = BachelorPreferences::getBenefitByHashKey($hash_code);

        if ($params[1] == SpecialMark::KEY) {
            $this->special_mark_code = $reference->ref_key;
            $this->special_mark_id = $reference->id;
        } else {
            $this->privilege_code = $reference->ref_key;
            $this->privilege_id = $reference->id;
        }
        if ($params[2] != 2) {
            $this->priority_right = $params[2];
        }
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

    




    public function hasEnlistedBachelorSpecialitiesQueryTemplate(string $bachelorSpecialitiesQueryGetter): bool
    {
        $tn = BachelorSpeciality::tableName();
        return $this->{$bachelorSpecialitiesQueryGetter}()
            ->andWhere(["{$tn}.is_enlisted" => true])
            ->exists();
    }

    


    public function hasEnlistedBachelorSpecialities(): bool
    {
        return $this->hasEnlistedBachelorSpecialitiesQueryTemplate('getBachelorSpecialities');
    }

    public function olympiadMatchedByCurriculum(StoredCurriculumReferenceType $curriculumReferenceType): bool
    {
        return $this->olympiad
            ->getOlympiadFilters()
            ->joinWith(['curriculumRef'])
            ->andWhere([
                StoredCurriculumReferenceType::tableName() . '.reference_uid' => $curriculumReferenceType->reference_uid
            ])
            ->exists();
    }

    


    public function hasEnlistedBachelorSpecialitiesWithOlympiad(): bool
    {
        return $this->hasEnlistedBachelorSpecialitiesQueryTemplate('getBachelorSpecialitiesWithOlympiad');
    }

    public function getContractor(): ActiveQuery
    {
        return $this->hasOne(Contractor::class, ['id' => 'contractor_id']);
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
        return 'contractor_id';
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
