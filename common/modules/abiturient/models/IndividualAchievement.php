<?php

namespace common\modules\abiturient\models;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\AttachmentManager;
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
use common\models\dictionary\IndividualAchievementType;
use common\models\dictionary\StoredReferenceType\StoredAchievementCategoryReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use common\models\errors\RecordNotValid;
use common\models\IndividualAchievementDocumentType;
use common\models\interfaces\ArchiveModelInterface;
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\interfaces\dynamic_validation_rules\IHavePropsRelatedToDocumentType;
use common\models\interfaces\FileToShowInterface;
use common\models\interfaces\IHaveDocumentCheckStatus;
use common\models\relation_presenters\AttachmentsRelationPresenter;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\traits\ArchiveTrait;
use common\models\traits\DocumentCheckStatusTrait;
use common\models\traits\FileAttachTrait;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\bachelor\EducationData;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\ICanAttachFile;
use common\modules\abiturient\models\interfaces\ICanBeStringified;
use common\modules\abiturient\models\repositories\IndividualAchievementDocumentTypesRepository;
use stdClass;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;





























class IndividualAchievement extends ChangeHistoryDecoratedModel
implements
    AttachmentLinkableEntity,
    ArchiveModelInterface,
    ApplicationConnectedInterface,
    IHasRelations,
    IHaveIdentityProp,
    ICanGivePropsToCompare,
    ICanAttachFile,
    IHavePropsRelatedToDocumentType,
    IHaveDocumentCheckStatus,
    ICanBeStringified
{
    use ArchiveTrait;
    use DocumentCheckStatusTrait;
    use FileAttachTrait;
    use HasDirtyAttributesTrait;
    use HtmlPropsEncoder;

    protected bool $_new_record = true;

    protected ?RulesProviderByDocumentType $_document_type_validation_extender = null;

    public $isFrom1C = false;

    public $not_found_contractor;

    const STATUS_STAGED = 1;
    const STATUS_UNSTAGED = 2;
    const STATUS_TO_DELETE = 0;
    const STATUS_ARCHIVED = 3;
    public const SCENARIO_RECOVER = 'recover';

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_document_type_validation_extender = new RulesProviderByDocumentType($this);
    }

    public static function tableName()
    {
        return '{{%individual_achievement}}';
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
                    'additional'
                ],
                'trim'
            ],
            [
                [
                    'user_id',
                    'dictionary_individual_achievement_id',
                    'document_type_id',
                    'status',
                    'contractor_id'
                ],
                'integer'
            ],
            [
                [
                    'user_id',
                    'dictionary_individual_achievement_id',
                    'document_type_id',
                ],
                'required'
            ],
            [
                [
                    'document_series',
                    'document_number'
                ],
                'string',
                'max' => 100
            ],
            [
                ['status'],
                'in',
                'range' => [
                    IndividualAchievement::STATUS_STAGED,
                    IndividualAchievement::STATUS_UNSTAGED,
                    IndividualAchievement::STATUS_TO_DELETE,
                    IndividualAchievement::STATUS_ARCHIVED
                ]
            ],
            [
                'status',
                'default',
                'value' => IndividualAchievement::STATUS_UNSTAGED
            ],
            [
                [
                    'document_date',
                    'additional'
                ],
                'string',
                'max' => 1000
            ],
            [
                'file',
                'safe',
                'on' => [IndividualAchievement::SCENARIO_RECOVER]
            ],
            [
                ['document_date'],
                'date',
                'format' => 'php:d.m.Y',
                'max' => date('d.m.Y')
            ],
            [
                ['document_check_status_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDocumentCheckStatusReferenceType::class,
                'targetAttribute' => ['document_check_status_ref_id' => 'id'],
            ],
            [
                [
                    'read_only',
                    'not_found_contractor',
                ],
                'boolean'
            ],
            [
                'read_only',
                'default',
                'value' => false
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
                'whenClient' => "function (model,attribute) {
                    return !+$(attribute.input).attr('data-skip_validation');
                }"
            ],
            [
                [
                    'document_date'
                ],
                'required',
                'when' => function ($model) {
                    return (!$model->isFrom1C);
                },
                'whenClient' => 'function (attribute, value) {
                    return true;
                }'
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[IndividualAchievement::SCENARIO_RECOVER] = $scenarios[IndividualAchievement::SCENARIO_DEFAULT];
        return $scenarios;
    }

    


    public function attributeLabels()
    {
        return [
            'file' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "file"; формы ИД: `Скан-копия подтверждающего документа`'),
            'user_id' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "user_id"; формы ИД: `Поступающий`'),
            'additional' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "additional"; формы ИД: `Дополнительно`'),
            'document_date' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "document_date"; формы ИД: `Дата выдачи`'),
            'contractor_id' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "contractor_id"; формы ИД: `Выдан`'),
            'document_number' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "document_number"; формы ИД: `Номер документа`'),
            'document_series' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "document_series"; формы ИД: `Серия документа`'),
            'document_type_id' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "document_type_id"; формы ИД: `Тип документа`'),
            'achievementTypeName' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "achievementTypeName"; формы ИД: `Тип достижения`'),
            'documentTypeDocumentDescription' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "documentTypeDocumentDescription"; формы ИД: `Тип документа`'),
            'dictionary_individual_achievement_id' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "dictionary_individual_achievement_id"; формы ИД: `Тип достижения`'),
            'attachments' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для файлов формы ИД: `Файл`'),
            'documentCheckStatus' => Yii::t('abiturient/bachelor/individual-achievement/individual-achievement', 'Подпись для поля "documentCheckStatus" формы ИД: `Статус проверки документа`'),
        ];
    }


    public function getAchievementType()
    {
        return $this->hasOne(IndividualAchievementType::class, ['id' => 'dictionary_individual_achievement_id']);
    }

    public function getDocumentType()
    {
        return $this->hasOne(IndividualAchievementDocumentType::class, ['id' => 'document_type_id']);
    }

    public function getFullDescription()
    {
        return trim(ArrayHelper::getValue($this, 'achievementType.fullDescription', '-') . ' ' .
            ArrayHelper::getValue($this, 'documentType.documentTypeRef.description', '-') .
            " {$this->document_series} {$this->document_number}" .
            " {$this->document_date} " . ArrayHelper::getValue($this, 'contractor.name', ''));
    }

    public function getRealDocumentType()
    {
        return $this->hasOne(DocumentType::class, ['id' => 'document_type_ref_id'])
            ->via('documentType');
    }

    


    public function getUser(): ActiveQuery
    {
        return $this->getRawUser()->andOnCondition(['user.is_archive' => false]);
    }

    


    public function getRawUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getUserInstance(): User
    {
        return $this->user ?: new User();
    }

    protected function getOwnerId()
    {
        return $this->user_id;
    }

    public function checkAccess($user)
    {
        if ($user->isModer()) {
            return true;
        } elseif ($user->id == $this->getOwnerId()) {
            return true;
        } else {
            return false;
        }
    }

    public function getFormated_document_date()
    {
        return date('Y-m-d', strtotime($this->document_date));
    }

    public function __set($name, $value)
    {
        $value = $this->encodeProp($name, $value);

        if ($name == 'document_date' && !empty($value)) {
            $value = (string)date('d.m.Y', strtotime($value));
        }
        parent::__set($name, $value);
    }

    


    public function getApplication(): ActiveQuery
    {
        return $this->hasOne(
            BachelorApplication::class,
            ['id' => 'application_id']
        );
    }

    public static function getTableLink(): string
    {
        return 'individual_achievement_attachment';
    }

    public static function getEntityTableLinkAttribute(): string
    {
        return 'individual_achievement_id';
    }

    public static function getAttachmentTableLinkAttribute(): string
    {
        return 'attachment_id';
    }

    public static function getModel(): string
    {
        return get_called_class();
    }

    public function getAttachmentType(): ?AttachmentType
    {
        return AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_INDIVIDUAL_ACHIEVEMENT);
    }


    public function getAttachments(): ActiveQuery
    {
        return $this->getRawAttachments()
            ->andOnCondition([Attachment::tableName() . '.deleted' => false]);
    }

    public function getRawAttachments(): ActiveQuery
    {
        return $this->hasMany(Attachment::class, ['id' => IndividualAchievement::getAttachmentTableLinkAttribute()])
            ->viaTable(IndividualAchievement::getTableLink(), [IndividualAchievement::getEntityTableLinkAttribute() => 'id']);
    }

    public function getName(): string
    {
        $return_str = Yii::t(
            'abiturient/bachelor/individual-achievement/individual-achievement',
            'Текст для построения наименования; формы ИД: `Индивидуальное достижение`'
        );
        $return_str = "{$return_str} ";
        if ($this->document_type_id != null) {
            $return_str .= $this->documentType->documentDescription;
        }
        $return_str .= " (Серия {$this->document_series} № {$this->document_number})";

        return $return_str;
    }

    public function stringify(): string
    {
        return $this->getName();
    }

    public function getAttachmentCollection(): FileToShowInterface
    {
        return new AttachedEntityAttachmentCollection(
            $this->user,
            $this,
            $this->getAttachmentType(),
            $this->attachments,
            $this->formName(),
            'file'
        );
    }

    public static function getDbTableSchema(): TableSchema
    {
        return IndividualAchievement::getTableSchema();
    }

    public function canDownload()
    {
        return $this->getAttachments()->exists();
    }

    


    public static function getArchiveColumn(): string
    {
        return 'status';
    }

    public static function getArchiveValue()
    {
        return IndividualAchievement::STATUS_ARCHIVED;
    }

    public function getAttachmentConnectors(): array
    {
        return ['application_id' => $this->application->id];
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'dictionary_individual_achievement_id' => function ($model) {
                return ArrayHelper::getValue($model, 'achievementTypeName');
            },
            'document_series',
            'document_number',
            'contractor_id' => function ($model) {
                return $model->contractor->name ?? '';
            },
            'document_type_id' => function ($model) {
                return ArrayHelper::getValue($model, 'documentTypeDocumentDescription');
            },
            'document_date',
            'additional',
        ];
    }

    public function getEntityIdentifier(): ?string
    {
        return $this->getName();
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_INDIVIDUAL_ACHIEVEMENT;
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            AttachmentManager::unlinkAllAttachment($this);
            return true;
        } else {
            return false;
        }
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->_new_record = false;
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    


















    public static function GetOrCreateFromRaw(
        string              $series,
        string              $number,
        string              $document_date,
        array               $organization,
        string              $additional,
        BachelorApplication $application,
        $achievement_category_ref,
        $achievement_document_type_ref,
        array               $documentCheckStatusRef = [],
        bool                $documentReadOnly = false,
        array               $ids_to_ignore = []
    ): IndividualAchievement {
        $user = $application->user;
        $campaign_ref = $application->type->rawCampaign->referenceType;
        $contractor = ContractorManager::GetOrCreateContractor($organization);
        
        $ind_arch_type = IndividualAchievementType::getIaTypesByCampaignAndSpecialitiesQuery($application, true)
            ->joinWith(['achievementCategoryRef a_c'])
            ->andWhere(['a_c.reference_uid' => ReferenceTypeManager::GetOrCreateReference(
                StoredAchievementCategoryReferenceType::class,
                $achievement_category_ref
            )->reference_uid])
            ->one();
        if (!$ind_arch_type) {
            
            $ind_arch_type = IndividualAchievementType::find()
                ->active()
                ->joinWith(['achievementCategoryRef a_c'])
                ->andWhere(['a_c.reference_uid' => ReferenceTypeManager::GetOrCreateReference(
                    StoredAchievementCategoryReferenceType::class,
                    $achievement_category_ref
                )->reference_uid])
                ->one();
        }
        if (!$ind_arch_type) {
            throw new UserException('Не найден подходящий тип индивидуального достижения, обратитесь к администратору');
        }
        $doc_type = null;
        if ($achievement_document_type_ref) {
            $real_doc_type = ReferenceTypeManager::GetOrCreateReference(DocumentType::class, $achievement_document_type_ref);
            if ($real_doc_type) {
                $doc_type = IndividualAchievementDocumentTypesRepository::GetDocumentTypesByIndividualAchievementTypeAndCampaignQuery(
                    $campaign_ref,
                    $ind_arch_type
                )
                    ->joinWith(['documentTypeRef doc_type'])
                    ->andWhere(['doc_type.ref_key' => $real_doc_type->ref_key,])
                    ->one();
            }
        }
        if (!$doc_type) {
            $doc_type_string = print_r($achievement_document_type_ref, true);
            throw new UserException("Не удалось определить тип документа {$doc_type_string}.");
        }
        $ind_arch = IndividualAchievement::find()
            ->joinWith(['documentType.documentTypeRef d_t', 'achievementType.achievementCategoryRef a_c'])
            ->active()
            ->andWhere([
                'document_series' => $series,
                'document_number' => $number,
                'user_id' => $user->id,
                'application_id' => $application->id
            ])
            ->andWhere(['a_c.reference_uid' => ArrayHelper::getValue($ind_arch_type, 'achievementCategoryRef.reference_uid')])
            ->andWhere(['d_t.ref_key' => ArrayHelper::getValue($doc_type, 'documentTypeRef.ref_key')])
            ->andWhere(['not', [IndividualAchievement::tableName() . '.id' => $ids_to_ignore]])
            ->one();

        if (!$ind_arch) {
            $ind_arch = new IndividualAchievement();
            $ind_arch->user_id = $user->id;
            $ind_arch->application_id = $application->id;
            $ind_arch->dictionary_individual_achievement_id = $ind_arch_type->id;
            $ind_arch->document_type_id = $doc_type->id;
        }
        $ind_arch->read_only = $documentReadOnly;
        $ind_arch->setDocumentCheckStatusFrom1CData($documentCheckStatusRef);

        $ind_arch->isFrom1C = true;
        $ind_arch->document_series = $series;
        $ind_arch->document_number = $number;
        $ind_arch->document_date = $document_date;
        $ind_arch->contractor_id = $contractor->id ?? null;
        $ind_arch->additional = $additional;
        $ind_arch->status = IndividualAchievement::STATUS_STAGED;

        if ($ind_arch->validate()) {
            if (empty($ind_arch->application)) {
                DraftsManager::SuspendHistory($ind_arch);
            }
            $ind_arch->save(false);
        } else {
            $error_msg = "Ошибка получения индивидуального достижения\n" . print_r($ind_arch->errors, true);
            Yii::error($error_msg, 'INDIVIDUAL_ACHIEVEMENTS_RECEIVING');
            throw new RecordNotValid($ind_arch);
        }
        return $ind_arch;
    }

    public function getRelationsInfo(): array
    {
        return [
            new AttachmentsRelationPresenter(
                'attachments',
                ['parent_instance' => $this]
            ),
        ];
    }

    public function getIdentityString(): string
    {
        $ia_type_uid = ArrayHelper::getValue($this, 'achievementType.achievementCategoryRef.reference_uid', '');
        $document_uid = ArrayHelper::getValue($this, 'documentType.documentTypeRef.ref_key', '');
        return "{$ia_type_uid}_{$document_uid}_{$this->document_series}_{$this->document_number}";
    }

    public function getAchievementTypeName()
    {
        return ArrayHelper::getValue($this, 'achievementType.name');
    }

    public function getDocumentTypeDocumentDescription()
    {
        return ArrayHelper::getValue($this, 'documentType.documentDescription');
    }

    public function getPropsToCompare(): array
    {
        return ArrayHelper::merge(array_diff(array_keys($this->attributes), ['user_id']), [
            'achievementTypeName',
            'documentTypeDocumentDescription'
        ]);
    }

    public function getIsActuallyNewRecord(): bool
    {
        return $this->_new_record;
    }

    public function getAttachedFilesInfo(): array
    {
        $files = [];
        foreach ($this->attachments as $achievement) {
            $files[] = [
                $achievement,
                ArrayHelper::getValue($this, 'documentType.documentTypeRef'),
                null
            ];
        }
        return $files;
    }

    public function getContractor(): ActiveQuery
    {
        return $this->hasOne(Contractor::class, ['id' => 'contractor_id']);
    }

    public function fillFromEducation(EducationData $educationData)
    {
        $this->document_series = $educationData->series;
        $this->document_number = $educationData->number;
        $this->document_date = $educationData->date_given;
        $this->contractor_id = $educationData->contractor_id;
        
        if (!$this->save()) {
            throw new RecordNotValid($this);
        }
        FilesManager::CopyFiles($educationData, $this);
    }

    public static function getDocumentTypePropertyName(): string
    {
        return 'realDocumentType';
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
        return 'additional';
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
