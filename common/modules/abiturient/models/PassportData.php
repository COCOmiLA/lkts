<?php

namespace common\modules\abiturient\models;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\AttachmentManager;
use common\components\CodeSettingsManager\CodeSettingsManager;
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
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\interfaces\dynamic_validation_rules\IHavePropsRelatedToDocumentType;
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
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ICanAttachFile;
use common\modules\abiturient\models\interfaces\ICanBeStringified;
use common\modules\abiturient\models\interfaces\QuestionaryConnectedInterface;
use common\modules\abiturient\models\parentData\ParentData;
use common\modules\abiturient\validators\PassportData\PassportDataValidation;
use stdClass;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;























class PassportData extends ChangeHistoryDecoratedModel
implements
    QuestionaryConnectedInterface,
    ChangeLoggedModelInterface,
    ArchiveModelInterface,
    IHaveIdentityProp,
    ICanGivePropsToCompare,
    AttachmentLinkableEntity,
    ICanAttachFile,
    IHasRelations,
    IHavePropsRelatedToDocumentType,
    IHaveDocumentCheckStatus,
    ICanBeStringified
{
    use ArchiveTrait;
    use FileAttachTrait;
    use HtmlPropsEncoder;
    use HasDirtyAttributesTrait;
    use DocumentCheckStatusTrait;

    const SCENARIO_GET_ANKETA = 'get_anketa';
    const SCENARIO_SIGN_UP = 'sign_up';
    const SCENARIO_CONTRACTOR_MAY_NOT_BE_FOUND = 'contractor_may_not_be_found';
    public const SCENARIO_NOT_REQUIRED = 'not_required';

    protected bool $_new_record = true;

    
    public $notFoundContractor;

    protected ?RulesProviderByDocumentType $_document_type_validation_extender = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_document_type_validation_extender = new RulesProviderByDocumentType($this);
    }

    public static function tableName()
    {
        return '{{%passport_data}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->_new_record = false;
    }

    public function ownRequiredRules(): array
    {
        $passport_rf_required = ['series', 'contractor_id'];
        return [
            [
                $passport_rf_required,
                'required',
                'when' => function ($model, $attribute) {
                    if (empty($model->documentType)) {
                        return false;
                    }
                    return ($model->documentType->ref_key == Yii::$app->configurationManager->getCode('russian_passport_guid'));
                },
                'whenClient' => "
                    function (attribute, value) {
                        $(attribute.input).parent('.form-group').removeClass('required');
                        $(attribute.input).parent('.form-group').find('label').removeClass('has-star');

                        var input = $('#{$this->clientFormName}-document_type_id');
                        if (input.length === 0) {
                            input = $('#{$this->clientFormName}-document_type_id_" . ($this->id ?? '0') . "');
                        }
                        if (
                            input.val() != '' &&
                            input.val() != " . CodeSettingsManager::GetEntityByCode('russian_passport_guid')->id . "
                        ) {
                            return false;
                        }

                        var result = true;
                        if (attribute === 'contractor_id') {
                            result = $('#{$this->clientFormName}-contractor_not_found-" . ($this->id ?? '0') . "').not(':checked');
                        }
                        if (result) {
                            $(attribute.input).parent('.form-group').addClass('required');
                            $(attribute.input).parent('.form-group').find('label').addClass('has-star');
                        }

                        return result;
                    }
                ",
                'except' => [
                    self::SCENARIO_NOT_REQUIRED
                ]
            ],
        ];
    }

    public static function baseRules()
    {
        return [
            [
                [
                    'series',
                    'number'
                ],
                'trim'
            ],
            [
                [
                    'archive',
                    'notFoundContractor',
                    'read_only',
                ],
                'boolean'
            ],
            [
                [
                    'archive',
                    'notFoundContractor',
                    'read_only',
                ],
                'default',
                'value' => false
            ],
            [
                [
                    'questionary_id',
                    'document_type_id',
                    'contractor_id'
                ],
                'integer'
            ],
            [
                ['issued_date'],
                'string',
                'max' => 100
            ],
            [
                [
                    'series',
                    'number'
                ],
                'string',
                'max' => 50
            ],
            [
                'document_type_id',
                'required',
                'except' => [
                    self::SCENARIO_NOT_REQUIRED
                ]
            ],
            [
                ['questionary_id'],
                'required',
                'except' => [
                    self::SCENARIO_GET_ANKETA,
                    self::SCENARIO_SIGN_UP,
                    self::SCENARIO_CONTRACTOR_MAY_NOT_BE_FOUND,
                    self::SCENARIO_NOT_REQUIRED
                ]
            ],
            [
                'country_id',
                'safe'
            ],
            [
                [
                    'series',
                    'number',
                ],
                PassportDataValidation::class,
                'except' => [self::SCENARIO_GET_ANKETA],
            ],
            [
                ['document_check_status_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredDocumentCheckStatusReferenceType::class,
                'targetAttribute' => ['document_check_status_ref_id' => 'id'],
            ],
        ];
    }

    


    public function rules()
    {
        return [...$this->_document_type_validation_extender->getRules(), ...static::baseRules()];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_GET_ANKETA] = ['questionary_id', 'document_type_id', 'contractor_id', 'issued_date', 'series', 'number', 'archive'];
        $scenarios[self::SCENARIO_SIGN_UP] = ['questionary_id', 'document_type_id', 'contractor_id', 'issued_date', 'series', 'number', 'archive', 'notFoundContractor'];
        $scenarios[self::SCENARIO_NOT_REQUIRED] = $scenarios[self::SCENARIO_DEFAULT];
        $scenarios[self::SCENARIO_CONTRACTOR_MAY_NOT_BE_FOUND] = $scenarios[self::SCENARIO_SIGN_UP];
        return $scenarios;
    }

    


    public function attributeLabels()
    {
        return [
            'number' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "number" формы "Паспортные данные": `Номер`'),
            'series' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "series" формы "Паспортные данные": `Серия`'),
            'issuedBy' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "issuedBy" формы "Паспортные данные": `Кем выдан`'),
            'country_id' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "country_id" формы "Паспортные данные": `Гражданство`'),
            'issued_date' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "issued_date" формы "Паспортные данные": `Когда выдан`'),
            'questionary_id' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "questionary_id" формы "Паспортные данные": `Анкета`'),
            'departmentCode' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "departmentCode" формы "Паспортные данные": `Код подразделения`'),
            'document_type_id' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "document_type_id" формы "Паспортные данные": `Тип документа`'),
            'documentTypeDescription' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "documentTypeDescription" формы "Паспортные данные": `Тип документа`'),
            'contractor_id' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "contractor_id" формы "Паспортные данные": `Кем выдан`'),
            'documentCheckStatus' => Yii::t('abiturient/questionary/passport-data', 'Подпись для поля "documentCheckStatus" формы "Паспортные данные": `Статус проверки документа`'),
        ];
    }

    public function getAbiturientQuestionary()
    {
        return $this->hasOne(AbiturientQuestionary::class, ['id' => 'questionary_id']);
    }

    public function getDocumentType()
    {
        return $this->hasOne(DocumentType::class, ['id' => 'document_type_id']);
    }

    public function getFormatted_issued_date()
    {
        return date('Y-m-d', strtotime($this->issued_date));
    }

    public function __set($name, $value)
    {
        $value = $this->encodeProp($name, $value);

        if ($name == 'issued_date') {
            $value = (string)date('d.m.Y', strtotime($value));
        }
        parent::__set($name, $value);
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'document_type_id' => function ($model) {
                return ArrayHelper::getValue($model, 'documentTypeDescription');
            },
            'issued_date' => function ($model) {
                return $model->issued_date == '01.01.1970' ? null : $model->issued_date;
            },
            'series',
            'number',
            'contractor_id' => function ($model) {
                return $model->contractor->name ?? '';
            }
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_PASSPORT_DATA;
    }

    public function getIssuedBy(): string
    {
        return $this->contractor->name ?? '';
    }

    public function getDepartmentCode(): string
    {
        return $this->contractor->subdivision_code ?? '';
    }

    public function getEntityIdentifier(): ?string
    {
        $doc = ArrayHelper::getValue($this, 'documentTypeDescription');
        return "{$doc} (Серия {$this->series} № {$this->number} выдан {$this->getIssuedBy()})";
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    















    public static function GetOrCreateFromRaw(
        ActiveRecord $model,
        string       $series,
        string       $number,
        string       $issued_date,
        array        $doc_organization,
        $document_type_ref,
        array        $documentCheckStatusRef = [],
        bool         $documentReadOnly = false,
        array        $ids_to_ignore = []
    ): PassportData {
        $docType = ReferenceTypeManager::GetOrCreateReference(DocumentType::class, $document_type_ref);
        $contractor = ContractorManager::GetOrCreateContractor($doc_organization);

        $passportDataTableName = PassportData::tableName();
        $local_passport = $model->getPassportData()
            ->joinWith(['documentType doc_type'])
            ->andWhere(['NOT', ["{$passportDataTableName}.id" => $ids_to_ignore]])
            ->andWhere([
                "{$passportDataTableName}.series" => $series,
                "{$passportDataTableName}.number" => $number,
            ])
            ->andFilterWhere([
                'doc_type.ref_key' => ($docType->ref_key ?? null),
            ])
            ->one();
        if (!$local_passport) {
            $local_passport = new static();
            static::setQuestionaryLink($local_passport, $model->id);
        }

        $local_passport->read_only = $documentReadOnly;
        $local_passport->setDocumentCheckStatusFrom1CData($documentCheckStatusRef);

        $local_passport->setScenarioForUpdateFromRaw();
        $local_passport->attributes = [
            'series' => $series,
            'number' => $number,
            'document_type_id' => ($docType->id ?? null),
            'issued_date' => $issued_date,
            'contractor_id' => ($contractor->id ?? null)
        ];
        $local_passport->archive = false;
        DraftsManager::SuspendHistory($local_passport);
        if (!$local_passport->save(false)) {
            throw new RecordNotValid($local_passport);
        }

        return $local_passport;
    }

    public function setScenarioForUpdateFromRaw()
    {
        $this->scenario = PassportData::SCENARIO_GET_ANKETA;
    }

    protected static function setQuestionaryLink(PassportData $model, ?int $questionary_id)
    {
        $model->questionary_id = $questionary_id;
    }

    public function getIdentityString(): string
    {
        $doc_type_uid = ArrayHelper::getValue($this, 'documentType.ref_key', '');
        return "{$this->series}_{$this->number}_{$doc_type_uid}";
    }

    public function getDocumentTypeDescription()
    {
        return ArrayHelper::getValue($this, 'documentType.description');
    }

    


    public function getClientFormName(): string
    {
        return mb_strtolower($this->formName());
    }

    public function getPropsToCompare(): array
    {
        return ArrayHelper::merge(
            array_diff(array_keys($this->attributes), ['document_type_id']),
            ['documentTypeDescription']
        );
    }

    public static function getTableLink(): string
    {
        return 'passport_attachment';
    }

    public static function getEntityTableLinkAttribute(): string
    {
        return 'passport_id';
    }

    public static function getAttachmentTableLinkAttribute(): string
    {
        return "attachment_id";
    }

    public static function getModel(): string
    {
        return get_called_class();
    }

    public static function getDbTableSchema(): TableSchema
    {
        return self::getTableSchema();
    }

    public function getAttachmentType(): ?AttachmentType
    {
        return AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_IDENTITY_DOCUMENT);
    }

    public function getAttachments(): ActiveQuery
    {
        return $this->getRawAttachments()
            ->andOnCondition([
                Attachment::tableName() . '.deleted' => false,
            ]);
    }

    public function getRawAttachments(): ActiveQuery
    {
        return $this->hasMany(Attachment::class, ['id' => self::getAttachmentTableLinkAttribute()])
            ->viaTable(self::getTableLink(), [
                self::getEntityTableLinkAttribute() => 'id'
            ]);
    }

    public function getName(): string
    {
        $name = Yii::t('abiturient/questionary/passport-data', 'Название сущности документов удостоверяющих личность поступающего: `Документ удостоверяющий личность`');
        return "{$name} {$this->getEntityIdentifier()}";
    }

    public function stringify(): string
    {
        return $this->getName();
    }

    public function getAttachmentCollection(): ?AttachedEntityAttachmentCollection
    {
        return new AttachedEntityAttachmentCollection($this->getUserInstance(), $this, $this->getAttachmentType(), $this->attachments, $this->formName(), 'file');
    }

    public function getAttachmentConnectors(): array
    {
        return [
            'questionary_id' => $this->abiturientQuestionary->id
        ];
    }

    public function getUserInstance(): User
    {
        return ArrayHelper::getValue($this, 'abiturientQuestionary.user') ?: new User();
    }

    public function getRelationsInfo(): array
    {
        return [
            new AttachmentsRelationPresenter('attachments', [
                'parent_instance' => $this,
            ]),
        ];
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            AttachmentManager::unlinkAllAttachment($this);
            return true;
        }
        return false;
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
                ArrayHelper::getValue($this, 'documentType'),
                null
            ];
        }
        return $files;
    }

    public function getContractor(): ActiveQuery
    {
        return $this->hasOne(Contractor::class, ['id' => 'contractor_id']);
    }

    public function isNotRegularIssueDate(): bool
    {
        $questionary = $this->abiturientQuestionary;
        if ($questionary && $questionary->personalData) {
            $issued_date_days = strtotime($this->issued_date) / (60 * 60 * 24);
            $birthdate_days = strtotime($questionary->personalData->birthdate) / (60 * 60 * 24);
            $diff_days = $issued_date_days - $birthdate_days;
            if (0 < $diff_days - 365 * 14 && $diff_days - 365 * 14 <= 100) {
                return false;
            }
            if (0 < $diff_days - 365 * 20 && $diff_days - 365 * 20 <= 100) {
                return false;
            }
            if (0 < $diff_days - 365 * 45 && $diff_days - 365 * 45 <= 100) {
                return false;
            }
            return true;
        }
        return false;
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
        return 'issued_date';
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
        return 'series';
    }

    public static function getDocumentNumberPropertyName(): string
    {
        return 'number';
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
