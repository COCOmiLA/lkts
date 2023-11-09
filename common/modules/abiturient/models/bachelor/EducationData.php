<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\ApplicationSendHandler\FullPacketSendHandler\SerializersForOneS\ContractorPackageBuilder;
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
use common\models\dictionary\EducationDataFilter;
use common\models\dictionary\EducationType;
use common\models\dictionary\StoredReferenceType\StoredDocumentCheckStatusReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationLevelReferenceType;
use common\models\dictionary\StoredReferenceType\StoredEducationReferenceType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\interfaces\ArchiveModelInterface;
use common\models\interfaces\AttachmentLinkableEntity;
use common\models\interfaces\dynamic_validation_rules\IHavePropsRelatedToDocumentType;
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
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;





































class EducationData extends ChangeHistoryDecoratedModel implements
    ApplicationConnectedInterface,
    ArchiveModelInterface,
    IHaveIdentityProp,
    ICanGivePropsToCompare,
    IHaveIgnoredOnCopyingAttributes,
    AttachmentLinkableEntity,
    ICanAttachFile,
    IHasRelations,
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

    const EMPTY_DATE = '0001-01-01';

    const SCENARIO_CONTRACTOR_MAY_NOT_BE_FOUND = 'contractor_may_not_be_found';

    public $tmp_uuid;

    public $notFoundContractor;

    protected bool $_new_record = true;

    protected ?RulesProviderByDocumentType $_document_type_validation_extender = null;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->_document_type_validation_extender = new RulesProviderByDocumentType($this);
    }

    public static function tableName()
    {
        return '{{%education_data}}';
    }

    public static function getBachelorSpecialityEducationDataTable()
    {
        return '{{%bachelor_speciality_education_data}}';
    }

    


    public static function find()
    {
        return new EnlistedApplicationQuery(get_called_class());
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

    


    public function rules()
    {
        $own_rules = [
            [
                [
                    'series',
                    'number',
                    'edu_end_year',
                ],
                'trim'
            ],
            [
                ['archived_at'],
                'integer'
            ],
            [
                [
                    'archive',
                    'have_original',
                    'read_only',
                    'notFoundContractor',
                    'original_from_epgu',
                ],
                'boolean'
            ],
            [
                [
                    'archived_at',
                    'application_id',
                    'education_type_id',
                    'education_level_id',
                    'document_type_id',
                    'education_ref_id',
                    'profile_ref_id',
                    'contractor_id',
                ],
                'integer'
            ],
            [
                [
                    'series',
                    'number',
                    'date_given',
                    'edu_end_year'
                ],
                'string',
                'max' => 100
            ],
            [
                [
                    'application_id',
                    'education_type_id',
                    'document_type_id',
                    'edu_end_year',
                ],
                'required'
            ],
            [
                'edu_end_year',
                'checkYear'
            ],
            [
                [
                    'read_only',
                    'have_original',
                    'original_from_epgu'
                ],
                'default',
                'value' => false
            ],
            [
                'series',
                'checkSeries'
            ],
            [
                'number',
                'checkNumber'
            ],
            [
                'date_given',
                'checkDate'
            ],
            [
                ['education_ref_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => StoredEducationReferenceType::class,
                'targetAttribute' => ['education_ref_id' => 'id']
            ],
            [
                ['profile_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredProfileReferenceType::class,
                'targetAttribute' => ['profile_ref_id' => 'id'],
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
                    return $('#education_data-contractor_not_found_" . ($this->id ?? 'new') . "').not(':checked');
                }",
            ],
            [
                [
                    'number',
                    'date_given',
                ],
                'required'
            ],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CONTRACTOR_MAY_NOT_BE_FOUND] = $scenarios[self::SCENARIO_DEFAULT];
        return $scenarios;
    }

    


    public function attributeLabels()
    {
        return [
            'number' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "number" формы "Док. об обр.": `Номер документа`'),
            'series' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "series" формы "Док. об обр.": `Серия документа`'),
            'date_given' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "date_given" формы "Док. об обр.": `Дата выдачи`'),
            'profileRef' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "profileRef" формы "Док. об обр.": `Профиль образования`'),
            'attachments' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для связи со скан-копиями: `Файлы`'),
            'edu_end_year' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "edu_end_year" формы "Док. об обр.": `Год окончания учебного заведения`'),
            'contractor_id' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "contractor_id" формы "Док. об обр.": `Наименование учебного заведения`'),
            'have_original' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "have_original" формы "Док. об обр.": `Вид предоставленного документа`'),
            'application_id' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "application_id" формы "Док. об обр.": `Заявление`'),
            'profile_ref_id' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "profile_ref_id" формы "Док. об обр.": `Профиль образования`'),
            'document_type_id' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "document_type_id" формы "Док. об обр.": `Тип документа`'),
            'education_type_id' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "education_type_id" формы "Док. об обр.": `Вид образования`'),
            'education_level_id' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "education_level_id" формы "Док. об обр.": `Уровень образования`'),
            'documentCheckStatus' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "documentCheckStatus" формы "Док. об обр.": `Статус проверки документа`'),
            'profileRefDescription' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "profileRefDescription" формы "Док. об обр.": `Профиль образования`'),
            'documentTypeDescription' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "documentTypeDescription" формы "Док. об обр.": `Тип документа`'),
            'educationTypeDescription' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "educationTypeDescription" формы "Док. об обр.": `Вид образования`'),
            'educationLevelDescription' => Yii::t('abiturient/bachelor/education/education-data', 'Подпись для поля "educationLevelDescription" формы "Док. об обр.": `Уровень образования`'),
        ];
    }

    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    public function getDocumentType()
    {
        return $this->hasOne(DocumentType::class, ['id' => 'document_type_id']);
    }

    public function getEducationType()
    {
        return $this->hasOne(EducationType::class, ['id' => 'education_type_id']);
    }

    public function getEducationLevel()
    {
        return $this->hasOne(StoredEducationLevelReferenceType::class, ['id' => 'education_level_id']);
    }

    


    public function getBachelorSpecialities(): ActiveQuery
    {
        return $this->hasMany(BachelorSpeciality::class, ['id' => 'bachelor_speciality_id'])
            ->viaTable(EducationData::getBachelorSpecialityEducationDataTable(), ['education_data_id' => 'id']);
    }

    




    public function getProfileRef()
    {
        return $this->hasOne(StoredProfileReferenceType::class, ['id' => 'profile_ref_id']);
    }

    public function getDescriptionString()
    {
        $series = trim($this->series);
        $number = trim($this->number);
        $dateGiven = trim($this->date_given);
        $schoolName = trim($this->schoolName);
        $result = trim(ArrayHelper::getValue($this, 'educationType.description'));
        if ($series) {
            $result .= Yii::t(
                'abiturient/bachelor/education/education-data',
                'Часть представления серии образования в выпадающем списки, на форме направлений подготовки: ` Серия {series}`',
                ['series' => $series]
            );
        }
        if ($number) {
            $result .= " №{$number}";
        }
        if ($dateGiven) {
            $result .= Yii::t(
                'abiturient/bachelor/education/education-data',
                'Часть представления даты выдачи образования в выпадающем списки, на форме направлений подготовки: ` от {dateGiven}`',
                ['dateGiven' => $dateGiven]
            );
        }
        if ($schoolName) {
            $result .= Yii::t(
                'abiturient/bachelor/education/education-data',
                'Часть представления выдавшей организации образования в выпадающем списки, на форме направлений подготовки: ` выданный {schoolName}`',
                ['schoolName' => $schoolName]
            );
        }

        return $result;
    }

    






    public static function build1sStructure(?EducationData $edu_data)
    {
        $doc = [
            'EducationDocumentTempGUID' => '',
            'EducationTypeRef' => ReferenceTypeManager::getEmptyRefTypeArray(),
            'Document' => [
                'DocumentTypeRef' => ReferenceTypeManager::getEmptyRefTypeArray(),
                'DocSeries' => '',
                'DocNumber' => '',
                'DocOrganization' => '',
                'IssueDate' => EducationData::EMPTY_DATE,
                'DocumentCheckStatusRef' => ReferenceTypeManager::GetReference($edu_data, 'notVerifiedStatusDocumentChecker'),
                'ReadOnly' => false,
            ],
            'GraduationYear' => '',
            'SubdivisionCode' => '',
            'EducationDocumentReferenceType' => ReferenceTypeManager::getEmptyRefTypeArray(),
            'ProfileRef' => ReferenceTypeManager::getEmptyRefTypeArray(),
        ];
        if ($edu_data) {
            $doc = [
                'EducationDocumentTempGUID' => $edu_data->tmp_uuid,
                'EducationTypeRef' => ReferenceTypeManager::GetReference($edu_data, 'educationType'),
                'Document' => [
                    'DocumentTypeRef' => ReferenceTypeManager::GetReference($edu_data, 'documentType'),
                    'DocSeries' => $edu_data->series,
                    'DocNumber' => $edu_data->number,
                    'DocOrganization' => (new ContractorPackageBuilder(null, $edu_data->contractor))->build(),
                    'IssueDate' => (string)$edu_data->formated_date_given,
                    'DocumentCheckStatusRef' => $edu_data->buildDocumentCheckStatusRefType(),
                    'ReadOnly' => $edu_data->read_only ? 1 : 0,
                ],
                'GraduationYear' => (int)$edu_data->edu_end_year,
                'SubdivisionCode' => '',
                'EducationDocumentReferenceType' => ReferenceTypeManager::GetReference($edu_data, 'educationRef'),
                'ProfileRef' => ReferenceTypeManager::getEmptyRefTypeArray(),
            ];

            if ($edu_data->profile_ref_id) {
                $doc['ProfileRef'] = ReferenceTypeManager::GetReference($edu_data, 'profileRef');
            }
        }

        return $doc;
    }

    public function checkYear($attribute, $params)
    {
        if (!((int)$this->$attribute > 1900 && (int)$this->$attribute < (int)(date('Y') + 1))) {
            $this->addError(
                $attribute,
                Yii::t(
                    'abiturient/bachelor/education/education-data',
                    'Подсказка с ошибкой для поля "edu_end_year" формы "Док. об обр.": `Год должен быть не ранее 1900 и не позднее текущего`'
                )
            );
        }
    }

    public function checkDate($attribute, $params)
    {
        $app = $this->application;
        $quest = null;
        if ($app) {
            $quest = $app->abiturientQuestionary;
        }
        if ($quest && $quest->personalData) {
            $date_given = strtotime($this->$attribute);
            $date_birth = strtotime($quest->personalData->birthdate);
            if ($date_given < $date_birth) {
                $this->addError(
                    $attribute,
                    Yii::t(
                        'abiturient/bachelor/education/education-data',
                        'Подсказка с ошибкой для поля "date_given" формы "Док. об обр.": `Дата выдачи документа должна быть позже даты рождения`'
                    )
                );
            }
        }
    }

    


















    public static function GetOrCreateFromRaw(
        string              $series,
        string              $number,
        array               $doc_organization,
        string              $date_given,
        string              $edu_end_year,
        $raw_education_ref,
        $raw_document_type_ref,
        $raw_education_type_ref,
        $rawEducationProfileRef,
        array               $documentCheckStatusRef = [],
        bool                $documentReadOnly = false,
        BachelorApplication $application
    ): EducationData {
        $eduRef = ReferenceTypeManager::GetOrCreateReference(
            StoredEducationReferenceType::class,
            $raw_education_ref
        );
        $contractor = ContractorManager::GetOrCreateContractor($doc_organization);
        $edu_data = null;
        if (!EmptyCheck::isEmpty($series) || !EmptyCheck::isEmpty($number)) {
            $edu_data = $application->getEducations()
                ->andWhere([
                    EducationData::tableName() . '.series' => $series,
                    EducationData::tableName() . '.number' => $number,
                ])->one();
        }
        if (empty($edu_data)) {
            $edu_data = new EducationData();
            $edu_data->application_id = $application->id;
        }
        $edu_data->read_only = $documentReadOnly;
        $edu_data->setDocumentCheckStatusFrom1CData($documentCheckStatusRef);

        $edu_data->education_ref_id = $eduRef->id ?? null;
        $edu_data->series = $series;
        $edu_data->number = $number;
        $edu_data->contractor_id = $contractor->id ?? null;
        $edu_data->date_given = $date_given;
        $edu_data->edu_end_year = $edu_end_year;
        $documentTypeRef = ReferenceTypeManager::GetOrCreateReference(
            DocumentType::class,
            $raw_document_type_ref
        );
        $educationTypeRef = ReferenceTypeManager::GetOrCreateReference(
            EducationType::class,
            $raw_education_type_ref
        );
        $educationProfileRef = ReferenceTypeManager::GetOrCreateReference(
            StoredProfileReferenceType::class,
            $rawEducationProfileRef
        );
        $edu_data->education_type_id = ArrayHelper::getValue($educationTypeRef, 'id');
        $edu_data->profile_ref_id = ArrayHelper::getValue($educationProfileRef, 'id');
        $edu_data->document_type_id = ArrayHelper::getValue($documentTypeRef, 'id');
        $edu_data->validate(); 
        $edu_data->save(false);
        return $edu_data;
    }

    public function getFormated_date_given()
    {
        return date('Y-m-d', strtotime($this->date_given));
    }

    public function __set($name, $value)
    {
        $value = $this->encodeProp($name, $value);

        if ($name == 'date_given') {
            $value = (string)date('d.m.Y', strtotime($value));
        }
        parent::__set($name, $value);
    }

    public function checkSeries($attribute, $params)
    {
        if ($this->documentType == null) {
            return true;
        }
        if (
            $this->documentType->ref_key != Yii::$app->configurationManager->getCode('edu_certificate_doc_type_guid')
            && $this->documentType->ref_key != Yii::$app->configurationManager->getCode('bak_doc_guid')
            && $this->documentType->ref_key != Yii::$app->configurationManager->getCode('mag_doc_guid')
            && $this->documentType->ref_key != Yii::$app->configurationManager->getCode('spec_doc_guid')
        ) {
            return true;
        }
        return false;
    }

    public function checkNumber($attribute, $params)
    {
        if ($this->documentType == null) {
            return;
        }

        if (
            $this->documentType->ref_key != Yii::$app->configurationManager->getCode('edu_certificate_doc_type_guid')
            && $this->documentType->ref_key != Yii::$app->configurationManager->getCode('bak_doc_guid')
            && $this->documentType->ref_key != Yii::$app->configurationManager->getCode('mag_doc_guid')
            && $this->documentType->ref_key != Yii::$app->configurationManager->getCode('spec_doc_guid')
        ) {
            return;
        }
        if ($this->documentType->ref_key == Yii::$app->configurationManager->getCode('edu_certificate_doc_type_guid') && mb_strlen((string)$this->$attribute, 'UTF-8') > 100) {
            $this->addError(
                $attribute,
                Yii::t(
                    'abiturient/bachelor/education/education-data',
                    'Подсказка с ошибкой для поля "number" формы "Док. об обр.": `Номер аттестата должен быть длиной не более 100 символов`'
                )
            );
        }
        if (($this->documentType->ref_key == Yii::$app->configurationManager->getCode('bak_doc_guid')
                || $this->documentType->ref_key == Yii::$app->configurationManager->getCode('mag_doc_guid')
                || $this->documentType->ref_key == Yii::$app->configurationManager->getCode('spec_doc_guid'))
            && mb_strlen((string)$this->$attribute, 'UTF-8') > 100
        ) {
            $this->addError(
                $attribute,
                Yii::t(
                    'abiturient/bachelor/education/education-data',
                    'Подсказка с ошибкой для поля "number" формы "Док. об обр.": `Номер диплома должен быть длиной не более 100 символов`'
                )
            );
        }
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'education_type_id' => function ($model) {
                return ArrayHelper::getValue($model->educationType, 'description');
            },
            'education_level_id' => function ($model) {
                return ArrayHelper::getValue($model->educationLevel, 'reference_name');
            },
            'document_type_id' => function ($model) {
                return ArrayHelper::getValue($model->documentType, 'description');
            },
            'series',
            'number',
            'date_given' => function ($model) {
                return $model->date_given == '01.01.1970' ? null : $model->date_given;
            },
            'edu_end_year',
            'contractor_id' => function ($model) {
                return $model->contractor->name ?? '';
            }
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_EDUCATION_DATA;
    }

    public function beforeArchive()
    {
        BachelorSpeciality::getDb()
            ->createCommand("DELETE FROM [[bachelor_speciality_education_data]] WHERE education_data_id = :id", ['id' => $this->id])
            ->execute();
        
        BachelorSpeciality::getDb()
            ->createCommand("UPDATE [[bachelor_speciality]] SET education_id = NULL WHERE education_id = :id", ['id' => $this->id])
            ->execute();
    }

    public function getEducationRef()
    {
        return $this->hasOne(StoredEducationReferenceType::class, ['id' => 'education_ref_id']);
    }

    public function getHaveOriginal()
    {
        if ($this->have_original) {
            return Yii::t(
                'abiturient/bachelor/education/education-data',
                'Подпись наличия флага "have_original" формы "Док. об обр.": `оригинал`'
            );
        } else {
            return Yii::t(
                'abiturient/bachelor/education/education-data',
                'Подпись отсутствия флага "have_original" формы "Док. об обр.": `копия`'
            );
        }
    }

    




    public function stringify(): string
    {
        return $this->getDescriptionString();
    }

    public function getRelationsInfo(): array
    {
        return [
            new AttachmentsRelationPresenter('attachments', [
                'parent_instance' => $this,
            ]),
        ];
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    public function getIdentityString(): string
    {
        $doc_type_uid = ArrayHelper::getValue($this, 'documentType.ref_key', '');
        return "{$this->series}_{$this->number}_{$doc_type_uid}";
    }

    public function getEducationTypeDescription()
    {
        return ArrayHelper::getValue($this, 'educationType.description');
    }

    public function getDocumentTypeDescription()
    {
        return ArrayHelper::getValue($this, 'documentType.description');
    }

    public function getProfileRefDescription()
    {
        return ArrayHelper::getValue($this, 'profileRef.reference_name', '');
    }

    public function getEducationLevelDescription()
    {
        return ArrayHelper::getValue($this, 'educationLevel.reference_name');
    }

    public function getPropsToCompare(): array
    {
        return [
            'educationLevelDescription',
            'series',
            'number',
            'date_given',
            'edu_end_year',
            'educationTypeDescription',
            'documentTypeDescription',
            'profileRefDescription',
        ];
    }

    


    public static function getRawProfileList(): array
    {
        $tnStoredProfileReferenceType = StoredProfileReferenceType::tableName();
        return StoredProfileReferenceType::find()
            ->notMarkedToDelete()
            ->active()
            ->andWhere(["{$tnStoredProfileReferenceType}.is_folder" => false])
            ->orderBy("{$tnStoredProfileReferenceType}.reference_name")
            ->all();
    }

    


    public static function getProfileList(): array
    {
        $profiles = EducationData::getRawProfileList();
        if (!$profiles) {
            return [];
        }

        return ArrayHelper::map($profiles, 'id', 'reference_name');
    }

    public function getIgnoredOnCopyingAttributes(): array
    {
        return [
            ...DraftsManager::$attributes_to_ignore,
            'application_id'
        ];
    }

    





    public function getAttributesToCompare(): array
    {
        return [
            'education_type_id',
            'education_level_id',
            'document_type_id',
            'profile_ref_id',
        ];
    }

    





    public function hasRelatedBachelorSpecialities(): bool
    {
        return $this->getBachelorSpecialities()->exists();
    }

    public static function getTableLink(): string
    {
        return 'education_attachment';
    }

    public static function getEntityTableLinkAttribute(): string
    {
        return 'education_id';
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
        return AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_EDUCATION_DOCUMENT);
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

    public function getName(): string
    {
        $doc_name = ArrayHelper::getValue($this, 'documentType.description');
        $name = Yii::t('abiturient/bachelor/education/education-data', 'Название сущности: `Документ об образовании`');
        return "{$name} {$doc_name} ({$this->series} {$this->number})";
    }

    public function getAttachmentCollection(): ?AttachedEntityAttachmentCollection
    {
        return new AttachedEntityAttachmentCollection($this->getUserInstance(), $this, $this->getAttachmentType(), $this->attachments, $this->formName(), 'file');
    }

    public function getAttachmentConnectors(): array
    {
        return [
            'application_id' => $this->application->id
        ];
    }

    public function getUserInstance(): User
    {
        return ArrayHelper::getValue($this, 'application.user') ?: new User();
    }

    public function getIsActuallyNewRecord(): bool
    {
        return $this->_new_record;
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            AttachmentManager::unlinkAllAttachment($this);
            $this->beforeArchive();
            return true;
        }
        return false;
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
        return 'date_given';
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

    


    public function hasBachelorSpecialities(): bool
    {
        return $this->getBachelorSpecialities()->exists();
    }

    


    public function hasEnlistedBachelorSpecialities(): bool
    {
        $tn = BachelorSpeciality::tableName();
        return $this->getBachelorSpecialities()
            ->andWhere(["{$tn}.is_enlisted" => true])
            ->exists();
    }

    public function getContractor(): ActiveQuery
    {
        return $this->hasOne(Contractor::class, ['id' => 'contractor_id']);
    }

    public function getSchoolName(): string
    {
        return $this->contractor->name ?? '';
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

    


    public static function getEducationTypeList(): array
    {
        $tnEducationType = EducationType::tableName();
        return ArrayHelper::map(
            EducationType::find()
                ->notMarkedToDelete()
                ->active()
                ->andWhere(["{$tnEducationType}.is_folder" => false])
                ->andWhere(["{$tnEducationType}.id" => EducationDataFilter::find()->select('education_type_id')]) 
                ->orderBy("{$tnEducationType}.description")
                ->all(),
            'id',
            'description'
        );
    }
}
