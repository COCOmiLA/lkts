<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\AttachmentManager;
use common\components\CodeSettingsManager\CodeSettingsManager;
use common\components\IndependentQueryManager\IndependentQueryManager;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\Attachment;
use common\models\attachment\attachmentCollection\ApplicationAttachmentCollection;
use common\models\attachment\attachmentCollection\AttachedEntityAttachmentCollection;
use common\models\AttachmentType;
use common\models\dictionary\AdmissionCategory;
use common\models\dictionary\DocumentType;
use common\models\dictionary\Speciality;
use common\models\dictionary\StoredReferenceType\StoredChildDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\EmptyCheck;
use common\models\errors\RecordNotValid;
use common\models\interfaces\ArchiveModelInterface;
use common\models\interfaces\AttachmentLinkableApplicationEntity;
use common\models\interfaces\CanUseMultiplyEducationDataInterface;
use common\models\interfaces\FileToShowInterface;
use common\models\interfaces\IHaveIgnoredOnCopyingAttributes;
use common\models\relation_presenters\AttachmentsRelationPresenter;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\relation_presenters\ManyToManyRelationPresenter;
use common\models\relation_presenters\OneToManyRelationPresenter;
use common\models\relation_presenters\OneToOneRelationPresenter;
use common\models\ToAssocCaster;
use common\models\traits\ArchiveTrait;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\models\User;
use common\models\validators\SpecialityOlympiadValidator;
use common\modules\abiturient\models\bachelor\AllAgreements\AgreementRecord;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\drafts\DraftsManager;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\File;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\ICanAttachFile;
use common\modules\abiturient\models\interfaces\ICanBeStringified;
use common\modules\abiturient\models\interfaces\IReceivedFile;
use common\modules\abiturient\modules\admission\models\ListChanceHeader;
use common\modules\abiturient\modules\admission\models\ListCompetitionHeader;
use common\modules\abiturient\modules\admission\models\ListCompetitionRow;
use DateTime;
use Yii;
use yii\base\UserException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Exception;
use yii\db\TableSchema;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;















































class BachelorSpeciality extends ChangeHistoryDecoratedModel
implements
    ApplicationConnectedInterface,
    AttachmentLinkableApplicationEntity,
    ArchiveModelInterface,
    IHasRelations,
    IHaveIdentityProp,
    ICanGivePropsToCompare,
    ICanAttachFile,
    IHaveIgnoredOnCopyingAttributes,
    CanUseMultiplyEducationDataInterface,
    ICanBeStringified
{
    use ArchiveTrait;
    use HasDirtyAttributesTrait;
    use HtmlPropsEncoder;

    protected static ?string $BUDGET_BASIS = null;
    protected static ?string $COMMERCIAL_BASIS = null;
    protected static ?string $TARGET_RECEPTION = null;

    public const ALLOW_SEVERAL_TARGET_RECEPTION = false;
    public const ALLOW_SEVERAL_PREFERENCE = false;

    public const SCENARIO_FULL_VALIDATION = 'scenario_full_validation';

    protected static ?string $MISSING_TARGET_RECEPTION_ERROR = null;
    protected static ?string $MISSING_PREFERENCE_ERROR = null;
    protected static ?string $MISSING_OLYMPIAD_ERROR = null;

    protected static ?string $PREFERENCE_FIELD_NAME_SPECIAL_RIGHT = null;
    protected static ?string $PREFERENCE_FIELD_NAME = null;

    protected bool $_new_record = true;

    public const EDUCATIONS_DATA_TAG_LIST_SEPARATOR = ',';

    public function afterFind()
    {
        parent::afterFind();
        $this->_new_record = false;
    }

    public function getIsActuallyNewRecord(): bool
    {
        return $this->_new_record;
    }

    public static function getBudgetBasis(): ?string
    {
        if (static::$BUDGET_BASIS === null) {
            static::$BUDGET_BASIS = Yii::$app->configurationManager->getCode('budget_basis_guid');
        }
        return static::$BUDGET_BASIS;
    }

    public static function getCommercialBasis(): ?string
    {
        if (static::$COMMERCIAL_BASIS === null) {
            static::$COMMERCIAL_BASIS = Yii::$app->configurationManager->getCode('full_cost_recovery_guid');
        }
        return static::$COMMERCIAL_BASIS;
    }

    public static function getTargetReceptionBasis(): ?string
    {
        if (static::$TARGET_RECEPTION === null) {
            static::$TARGET_RECEPTION = Yii::$app->configurationManager->getCode('target_reception_guid');
        }
        return static::$TARGET_RECEPTION;
    }

    public static function getMissingTargetReceptionError(): ?string
    {
        if (static::$MISSING_TARGET_RECEPTION_ERROR === null) {
            static::$MISSING_TARGET_RECEPTION_ERROR = Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'Подсказка с ошибкой для поля "target_reception_id" формы "НП": `Необходимо указать целевой договор для направления подготовки`'
            );
        }
        return static::$MISSING_TARGET_RECEPTION_ERROR;
    }

    public static function getMissingPreferenceError(): ?string
    {
        if (static::$MISSING_PREFERENCE_ERROR === null) {
            static::$MISSING_PREFERENCE_ERROR = Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'Подсказка с ошибкой для поля "preference_id" формы "НП": `Необходимо указать льготу или преимущественное право для направления подготовки`'
            );
        }
        return static::$MISSING_PREFERENCE_ERROR;
    }

    public static function getMissingOlympiadError(): ?string
    {
        if (static::$MISSING_OLYMPIAD_ERROR === null) {
            static::$MISSING_OLYMPIAD_ERROR = Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'Подсказка с ошибкой для поля "bachelor_olympiad_id" формы "НП": `Необходимо указать преимущественное право для направления подготовки`'
            );
        }
        return static::$MISSING_OLYMPIAD_ERROR;
    }

    public static function getPreferenceFieldNameSpecialRight(): ?string
    {
        if (static::$PREFERENCE_FIELD_NAME_SPECIAL_RIGHT === null) {
            static::$PREFERENCE_FIELD_NAME_SPECIAL_RIGHT = Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'Подпись поля "льгота особое право" формы "НП": `Льгота`'
            );
        }
        return static::$PREFERENCE_FIELD_NAME_SPECIAL_RIGHT;
    }

    public static function getPreferenceFieldName(): ?string
    {
        if (static::$PREFERENCE_FIELD_NAME === null) {
            static::$PREFERENCE_FIELD_NAME = Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'Подпись поля "льгота" формы "НП": `Льгота/особое право`'
            );
        }
        return static::$PREFERENCE_FIELD_NAME;
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public static function tableName()
    {
        return '{{%bachelor_speciality}}';
    }

    public static function getBachelorSpecialityEducationDataTable()
    {
        return '{{%bachelor_speciality_education_data}}';
    }

    


    public function rules()
    {
        return [
            [['archived_at'], 'integer'],
            [
                [
                    'archive',
                    'readonly',
                    'is_enlisted',
                    'is_without_entrance_tests',
                ],
                'boolean'
            ],
            [
                'is_enlisted',
                'default',
                'value' => false,
            ],
            [
                [
                    'education_id',
                    'application_id',
                    'speciality_id',
                    'priority',
                    'preference_id',
                    'cget_entrance_test_set_id',
                    'target_reception_id',
                    'budget_level_id',
                    'admission_category_id',
                    'sent_to_one_s_at',
                    'bachelor_olympiad_id',
                ],
                'integer'
            ],
            [
                [
                    'application_id',
                    'speciality_id'
                ],
                'required'
            ],
            [
                [
                    'application_code',
                ],
                'string',
                'max' => 1000
            ],
            [
                ['paid_contract_guid'],
                'string',
                'max' => 255
            ],
            [
                ['admission_category_id', 'target_reception_id'], 
                'validateCategory',
                'on' => [self::SCENARIO_FULL_VALIDATION]
            ],
            [
                ['admission_category_id'],
                'required',
                'when' => function (BachelorSpeciality $model, $attr) {
                    if ($model->speciality && $model->speciality->special_right) {
                        return false;
                    }

                    $eduSource = $model->speciality->educationSourceRef ?? null;
                    if (isset($eduSource) && $eduSource->reference_uid != static::getBudgetBasis()) {
                        return false;
                    }
                    return true;
                },
                'on' => self::SCENARIO_FULL_VALIDATION
            ],
            [
                ['educationsDataTagList'],
                'required',
                'when' => function ($model) {
                    if (!$model->application) {
                        return false;
                    }
                    return !$model->application->type->rawCampaign->common_education_document;
                },
                'on' => self::SCENARIO_FULL_VALIDATION
            ],
            [
                ['educationsDataTagList'],
                'safe',
            ],
            [
                ['bachelor_olympiad_id'],
                SpecialityOlympiadValidator::class,
                'on' => self::SCENARIO_FULL_VALIDATION
            ],
            [
                ['target_reception_id'],
                'required',
                'when' => function ($model) {
                    $eduSource = $model->speciality->educationSourceRef ?? null;
                    if (isset($eduSource) && $eduSource->reference_uid == static::getTargetReceptionBasis()) {
                        return true;
                    }
                    return false;
                },
                'on' => static::SCENARIO_FULL_VALIDATION,
                'message' => static::getMissingTargetReceptionError()
            ],
            [
                ['target_reception_id'],
                'validateTargetExists',
                'skipOnError' => false,
                'when' => function ($model) {
                    $eduSource = $model->speciality->educationSourceRef ?? null;
                    if (isset($eduSource) && $eduSource->reference_uid == static::getTargetReceptionBasis()) {
                        return true;
                    }
                    return false;
                },
                'on' => self::SCENARIO_FULL_VALIDATION
            ],
            [
                ['preference_id'],
                'required',
                'when' => function ($model, $attr) {
                    $cat = $model->admissionCategory;
                    $eduSource = $model->speciality->educationSourceRef ?? null;
                    if (isset($eduSource) && $eduSource->reference_uid != static::getBudgetBasis()) {
                        return false;
                    }
                    if (isset($cat) && $cat->ref_key == Yii::$app->configurationManager->getCode('category_all')) {
                        return false;
                    }
                    return true;
                },
                'on' => static::SCENARIO_FULL_VALIDATION,
                'message' => static::getMissingPreferenceError()
            ],
            [
                ['preference_id'],
                'validatePreferenceExists',
                'when' => function ($model, $attr) {
                    $cat = $model->admissionCategory;
                    $eduSource = $model->speciality->educationSourceRef ?? null;
                    if (isset($eduSource) && $eduSource->reference_uid != static::getBudgetBasis()) {
                        return false;
                    }
                    if (isset($cat) && $cat->ref_key == Yii::$app->configurationManager->getCode('category_all')) {
                        return false;
                    }
                    return true;
                },
                'on' => static::SCENARIO_FULL_VALIDATION,
            ],
            [
                ['preference_id'],
                'validatePreferences',
                'skipOnError' => false,
                'when' => function ($model) {
                    $cat = $model->admissionCategory;
                    $eduSource = $model->speciality->educationSourceRef ?? null;
                    if (
                        isset($eduSource) &&
                        isset($cat) &&
                        $eduSource->reference_uid == static::getBudgetBasis() &&
                        $cat->ref_key != Yii::$app->configurationManager->getCode('category_all')
                    ) {
                        return true;
                    }
                    return false;
                },
                'on' => static::SCENARIO_FULL_VALIDATION
            ],
            [
                ['target_reception_id'],
                'validateTargets',
                'skipOnError' => false,
                'when' => function ($model) {
                    if (static::ALLOW_SEVERAL_TARGET_RECEPTION) {
                        $eduSource = $model->speciality->educationSourceRef ?? null;
                        if (isset($eduSource) && $eduSource->reference_uid == static::getTargetReceptionBasis()) {
                            return true;
                        }
                    }
                    return false;
                },
                'on' => static::SCENARIO_FULL_VALIDATION
            ],
            [
                ['bachelor_olympiad_id'],
                'required',
                'when' => function (BachelorSpeciality $model, $attr) {
                    return $model->getIsWithoutEntranceTests();
                },
                'on' => static::SCENARIO_FULL_VALIDATION,
                'message' => static::getMissingOlympiadError()
            ],
            [
                ['bachelor_olympiad_id'],
                'validateOlympiads',
                'when' => function (BachelorSpeciality $model, $attr) {
                    return $model->getIsWithoutEntranceTests();
                },
                'skipOnError' => false,
                'on' => static::SCENARIO_FULL_VALIDATION
            ],
            [
                'readonly',
                'default',
                'value' => false
            ],
            [
                ['admission_category_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => AdmissionCategory::class,
                'targetAttribute' => ['admission_category_id' => 'id']
            ],
            [
                ['education_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => EducationData::class,
                'targetAttribute' => ['education_id' => 'id']
            ],
            [
                ['bachelor_olympiad_id'],
                'exist',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'targetClass' => BachelorPreferences::class,
                'targetAttribute' => ['bachelor_olympiad_id' => 'id']
            ],
            [
                ['cget_entrance_test_set_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => CgetEntranceTestSet::class,
                'targetAttribute' => ['cget_entrance_test_set_id' => 'id']
            ],

        ];
    }

    public function validateCategory($attribute, $params)
    {
        if ($this->admissionCategory) {
            $uid = $this->admissionCategory->ref_key;

            if (!$this->speciality->checkCategory($uid)) {
                $this->addError(
                    $attribute,
                    Yii::t(
                        'abiturient/bachelor/application/bachelor-speciality',
                        'Подсказка с ошибкой для поля "admission_category_id | target_reception_id" формы "НП": `Невозможно сохранить в заявление выбранный конкурс. В этапах приемной кампании отсутствует выбранный конкурс ({specialityFacultyName}, {specialitySpecialityName}, {specialityEduformName}, {specialityFinanceName}, Особая группа: {specialityDetailGroupCode}, {categoryName}). Обратитесь к администратору.`',
                        [
                            'categoryName' => $this->admissionCategory->description,
                            'specialityEduformName' => $this->speciality->educationFormRef->reference_name ?? '',
                            'specialityFacultyName' => $this->speciality->subdivisionRef->reference_name ?? '',
                            'specialityFinanceName' => $this->speciality->educationSourceRef->reference_name ?? '',
                            'specialitySpecialityName' => $this->speciality->directionRef->reference_name ?? '',
                            'specialityDetailGroupCode' => $this->speciality->detailGroupRef->reference_id ?? '',
                        ]
                    )
                );
            }
        }
    }

    


    public function attributeLabels()
    {
        return [
            'priority' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "priority" формы "НП": `Приоритет`'),
            'education_id' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "education_id" формы "НП": `Данные об образовании`'),
            'admission_category_id' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "admission_category_id" формы "НП": `Категория приема`'),
            'preference_id' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "preference_id" формы "НП": `Льгота`'),
            'bachelor_olympiad_id' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "bachelor_olympiad_id" формы "НП": `Олимпиада`'),
            'speciality_id' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "speciality_id" формы "НП": `Направление подготовки`'),
            'application_id' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "application_id" формы "НП": `Заявление`'),
            'budget_level_id' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "budget_level_id" формы "НП": `Уровень бюджета`'),
            'specialityString' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "specialityString" формы "НП": `Направление подготовки`'),
            'target_reception_id' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "target_reception_id" формы "НП": `Целевое направление`'),
            'admissionCategoryName' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "admissionCategoryName" формы "НП": `Категория приема`'),
            'is_without_entrance_tests' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "is_without_entrance_tests" формы "НП": `Поступление без вступительных испытаний`'),
            'isWithoutEntranceTestsDescription' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "is_without_entrance_tests" формы "НП": `Поступление без вступительных испытаний`'),
            'admissionAgreements' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "admissionAgreements" формы "НП": `Согласие на зачисление`'),
            'educationData' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "educationData" формы "НП": `Данные об образовании`'),
            'educationsDataTagList' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "educationsDataTagList" формы "НП": `Данные об образовании`'),
            'preference' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "preference" формы "НП": `Льгота`'),
            'olympiad' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "olympiad" формы "НП": `Олимпиада`'),
            'targetReception' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "targetReception" формы "НП": `Целевое направление`'),
            'is_enlisted' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для поля "is_enlisted" формы "НП": `Зачислен`'),
            'specialityPriority' => Yii::t('abiturient/bachelor/application/bachelor-speciality', 'Подпись для связи с приоритетом направления: `Приоритет`'),
        ];
    }

    public function validateOlympiads($attribute, $params)
    {
        $bachelorOlympiad = $this->bachelorOlympiad;
        if (!$bachelorOlympiad) {
            $this->addError(
                $attribute,
                Yii::t(
                    'abiturient/bachelor/application/bachelor-speciality',
                    'Подсказка с ошибкой для поля "bachelor_olympiad_id" формы "НП": `Требуется заполнить основание для поступления без вступительных испытаний'
                )
            );
            return;
        }
        if ($this->speciality && $this->speciality->curriculumRef) {
            if (!$bachelorOlympiad->olympiadMatchedByCurriculum(ArrayHelper::getValue($this, 'speciality.curriculumRef'))) {
                $this->addError(
                    $attribute,
                    Yii::t(
                        'abiturient/bachelor/application/bachelor-speciality',
                        'Подсказка с ошибкой для поля "bachelor_olympiad_id" формы "НП": `Для указанного направления подготовки данная олимпиада не может быть учтена для приема без вступительных испытаний!`'
                    )
                );
            }

            $prefs = $this->application->getSpecialities()
                ->innerJoinWith(['speciality.curriculumRef'])
                ->innerJoinWith('bachelorOlympiad')
                ->andWhere(['not', [StoredCurriculumReferenceType::tableName() . '.reference_uid' => $this->speciality->curriculumRef->reference_uid]])
                ->andWhere(['not', [BachelorSpeciality::tableName() . '.id' => $this->id]])
                ->andWhere([BachelorPreferences::tableName() . '.id' => $this->bachelor_olympiad_id])
                ->exists();
            if ($prefs) {
                $this->addError(
                    $attribute,
                    Yii::t(
                        'abiturient/bachelor/application/bachelor-speciality',
                        'Подсказка с ошибкой для поля "bachelor_olympiad_id" формы "НП": `Результат олимпиады можно использовать для поступления без вступительных испытаний только на одну образовательную программу.`'
                    )
                );
            }
        }
    }

    public function validatePreferences($attribute, $params)
    {
        $bachelorPreference = BachelorPreferences::findOne([
            'id' => $this->$attribute
        ]);
        if (isset($bachelorPreference)) {
            if (self::ALLOW_SEVERAL_PREFERENCE) {
                $prefs = BachelorSpeciality::find()
                    ->active()
                    ->andWhere([BachelorSpeciality::tableName() . '.preference_id' => $this->$attribute])
                    ->joinWith('preference')
                    ->andWhere([BachelorSpeciality::tableName() . '.application_id' => $this->application_id])
                    ->andWhere(['bachelor_preferences.olympiad_id' => null])
                    ->andWhere(['not', [BachelorSpeciality::tableName() . '.id' => $this->id]])
                    ->exists();
                if ($prefs) {
                    $this->addError(
                        $attribute,
                        Yii::t(
                            'abiturient/bachelor/application/bachelor-speciality',
                            'Подсказка с ошибкой для поля "preference_id" формы "НП": `Одну льготу можно использовать только для одного направления подготовки`'
                        )
                    );
                }
            }
        } else {
            $this->addError(
                $attribute,
                Yii::t(
                    'abiturient/bachelor/application/bachelor-speciality',
                    'Подсказка с ошибкой для поля "preference_id" формы "НП": `Ошибка системы. Не найдено соответствующей льготы / олимпиады.`'
                )
            );
        }
    }

    public function validateTargets($attribute, $params)
    {
        $prefs = BachelorSpeciality::find()
            ->active()
            ->andWhere([BachelorSpeciality::tableName() . '.target_reception_id' => $this->$attribute])
            ->andWhere([BachelorSpeciality::tableName() . '.application_id' => $this->application_id])
            ->andWhere(['not', [BachelorSpeciality::tableName() . '.id' => $this->id]]);

        if ($prefs->exists()) {
            $this->addError(
                $attribute,
                Yii::t(
                    'abiturient/bachelor/application/bachelor-speciality',
                    'Подсказка с ошибкой для поля "target_reception_id" формы "НП": `Одно целевое направление можно использовать только для одного направления подготовки`'
                )
            );
        }
    }

    


    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    


    public function getEducationsData(): ActiveQuery
    {
        return $this->hasMany(EducationData::class, ['id' => 'education_data_id'])
            ->viaTable(BachelorSpeciality::getBachelorSpecialityEducationDataTable(), ['bachelor_speciality_id' => 'id']);
    }

    


    public function getEducationsDataTagList(): ?array
    {
        $educationsData = $this->educationsData;
        if (!$educationsData) {
            return null;
        }

        return array_map(
            function (EducationData $educationData) {
                return $educationData->id;
            },
            $educationsData
        );
    }

    




    public function setEducationsDataTagList($educationsDataTagList): void
    {
        if ($this->is_enlisted) {
            return;
        }

        if (!is_array($educationsDataTagList)) {
            $educationsDataTagList = [$educationsDataTagList];
        }

        $this->unlinkAll('educationsData', true);
        foreach ($educationsDataTagList as $educationDataId) {
            $educationData = EducationData::findOne(['id' => $educationDataId]);
            if (!$educationData) {
                continue;
            }

            $this->link('educationsData', $educationData);
        }
    }

    


    public function getCgetEntranceTestSet()
    {
        return $this->hasOne(CgetEntranceTestSet::class, ['id' => 'cget_entrance_test_set_id']);
    }

    


    public function getRawBachelorEntranceTestSets()
    {
        return $this->hasMany(BachelorEntranceTestSet::class, ['bachelor_speciality_id' => 'id']);
    }

    


    public function getBachelorEntranceTestSets()
    {
        return $this->getRawBachelorEntranceTestSets()
            ->active();
    }

    public function getAnyAgreements()
    {
        return $this->hasMany(AdmissionAgreement::class, ['speciality_id' => 'id'])
            ->orderBy([AdmissionAgreement::tableName() . '.archive' => SORT_ASC, AdmissionAgreement::tableName() . '.created_at' => SORT_DESC]);
    }

    


    public function getAgreement()
    {
        return $this->hasOne(AdmissionAgreement::class, ['speciality_id' => 'id'])
            ->andWhere(['!=', 'admission_agreement.status', AdmissionAgreement::STATUS_MARKED_TO_DELETE])
            ->active();
    }

    public function getAgreementDecline()
    {
        $agreement = $this->getRawAgreements()
            ->limit(1)
            ->one();
        if ($agreement && $agreement->status == AdmissionAgreement::STATUS_MARKED_TO_DELETE) {
            return $agreement->getAgreementDecline();
        }
        return null;
    }

    public function getRawAgreementDecline()
    {
        return $this->hasOne(AgreementDecline::class, ['agreement_id' => 'id'])
            ->via('anyAgreements')
            ->orderBy([AgreementDecline::tableName() . '.archive' => SORT_ASC, AgreementDecline::tableName() . '.created_at' => SORT_DESC]);
    }

    public function getRawAgreements()
    {
        return $this->getAnyAgreements()
            ->active();
    }

    public function getDeletedAgreements()
    {
        return $this->getAnyAgreements()
            ->andWhere([AdmissionAgreement::tableName() . '.status' => AdmissionAgreement::STATUS_MARKED_TO_DELETE])
            ->active()
            ->orderBy([AdmissionAgreement::tableName() . '.created_at' => SORT_DESC]);
    }

    



    public function getEducation()
    {
        return $this->hasOne(EducationData::class, ['id' => 'education_id'])
            ->active();
    }

    public function getAgreementToDelete()
    {
        $agreement_id = $this->getRawAgreements()
            ->limit(1)
            ->select([AdmissionAgreement::tableName() . '.id'])
            ->column();
        if ($agreement_id) {
            return AdmissionAgreementToDelete::find()
                ->where([
                    'agreement_id' => $agreement_id
                ])
                ->active();
        }
        return null;
    }

    public function getPreference()
    {
        return $this->hasOne(BachelorPreferences::class, ['id' => 'preference_id'])
            ->andWhere([BachelorPreferences::tableName() . '.archive' => false]);
    }

    public function getBachelorOlympiad()
    {
        if (!$this->is_without_entrance_tests) {
            $this->bachelor_olympiad_id = null;
        }
        return $this->hasOne(BachelorPreferences::class, ['id' => 'bachelor_olympiad_id'])
            ->andWhere([BachelorPreferences::tableName() . '.archive' => false]);
    }

    public function getTargetReception()
    {
        return $this->hasOne(BachelorTargetReception::class, ['id' => 'target_reception_id'])
            ->andWhere([BachelorTargetReception::tableName() . '.archive' => false]);
    }

    public function getSpeciality()
    {
        return $this->hasOne(Speciality::class, ['id' => 'speciality_id']);
    }

    public function getSpecialityPriority()
    {
        return $this->hasOne(SpecialityPriority::class, ['bachelor_speciality_id' => 'id']);
    }

    public function getBudgetLevel()
    {
        return $this->hasOne(Speciality::class, ['id' => 'budget_level_id']);
    }

    




    protected function buildBenefitDocument($benefit)
    {
        $doc = [
            'DocumentType' => '',
            'DocumentTypeRef' => ReferenceTypeManager::getEmptyRefTypeArray(),
            'DocumentSeries' => '',
            'DocumentNumber' => '',
            'DocumentDate' => '0001-01-01',
            'DocumentOrganization' => '',
        ];
        if (!empty($benefit)) {
            $doc = [
                'DocumentType' => ArrayHelper::getValue($benefit, 'documentType.code', ''),
                'DocumentTypeRef' => ReferenceTypeManager::GetReference($benefit, 'documentType'),
                'DocumentSeries' => $benefit->document_series,
                'DocumentNumber' => $benefit->document_number,
                'DocumentDate' => date('Y-m-d', strtotime($benefit->document_date)),
                'DocumentOrganization' => $benefit->document_organization,
            ];
        }
        return $doc;
    }

    public function checkBalls()
    {
        $application = $this->application;
        $applicationType = $application->type;

        if (!$applicationType['enable_check_ege'] || $this->getIsWithoutEntranceTests()) {
            return null;
        }
        $disciplineEgeForm = Yii::$app->configurationManager->getCode('discipline_ege_form');

        
        $egeResults = $application->getEgeResults()
            ->joinWith('cgetExamForm cget_exam_form')
            ->with(['cgetDiscipline', 'cgetChildDiscipline'])
            ->andWhere(['cget_exam_form.reference_uid' => $disciplineEgeForm])
            ->all();
        if (empty($egeResults)) {
            return null;
        }
        $subjectRefIds = ArrayHelper::map($application->egeResults, 'id', 'cget_discipline_id');
        $tests = CgetEntranceTest::find()
            ->andWhere(['in', 'subject_ref_id', $subjectRefIds])
            ->andWhere(['cget_entrance_test_set_id' => $this->cget_entrance_test_set_id])
            ->andWhere(['entrance_test_result_source_ref_id' => $disciplineEgeForm])
            ->orderBy(['min_score' => SORT_DESC])
            ->all();
        if (empty($tests)) {
            return null;
        }
        $errors = [];
        foreach ($tests as $test) {
            
            foreach ($egeResults as $egeResult) {
                if ($test->subject_ref_id != $egeResult->cget_discipline_id) {
                    continue;
                }
                if ($test->min_score > $egeResult->discipline_points) {
                    $egeName = $egeResult->cgetDiscipline->reference_name;
                    if ($egeResult->hasChildren()) {
                        $egeName = $egeResult->cgetChildDiscipline->reference_name;
                    }

                    $disciplinePoints = !empty($egeResult->discipline_points) ? $egeResult->discipline_points : Yii::t(
                        'abiturient/bachelor/application/bachelor-speciality',
                        'Текс выводимый вместо пустого значения для поля "discipline_points" формы "НП": `не указано`'
                    );
                    $errors[] = Yii::t(
                        'abiturient/bachelor/application/bachelor-speciality',
                        'Текс ошибки при проверке минимального бала в форме "НП": `Баллы по предмету `{egeName}` ({disciplinePoints}) меньше минимальной границы в {testMinScore}`',
                        [
                            'egeName' => $egeName,
                            'testMinScore' => $test->min_score,
                            'disciplinePoints' => $disciplinePoints,
                        ]
                    );
                }
            }
        }
        $checkEgeErrors = null;
        if ($errors) {
            $checkEgeErrors = [
                'name' => $this->speciality->speciality_human_code . ' ' . $this->speciality->speciality_name,
                'errors' => $errors,
            ];
        }
        return $checkEgeErrors;
    }

    public function beforeValidate()
    {
        if (!$this->is_without_entrance_tests) {
            $this->bachelor_olympiad_id = null;
        }
        return parent::beforeValidate();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if (!empty($this->admissionCategory)) {
            $cat = $this->admissionCategory;
            if ($cat->ref_key == Yii::$app->configurationManager->getCode('category_all')) {
                $this->preference_id = null;
            }
        } else if (ArrayHelper::getValue($this, 'speciality.educationSourceRef.reference_uid') == Yii::$app->configurationManager->getCode('full_cost_recovery_guid')) {
            $catUid = Yii::$app->configurationManager->getCode('category_all');
            $cat = AdmissionCategory::find()->where(['ref_key' => $catUid])->andWhere(['archive' => false])->limit(1)->one();
            $this->admission_category_id = $cat->id;
        }
        return true;
    }

    public function beforeArchive()
    {
        $agreements = $this->getRawAgreements()->all();
        if ($agreements) {
            foreach ($agreements as $dataToDelete) {
                $dataToDelete->archive();
            }
        }

        
        $bachelorEntranceTestSets = $this->getBachelorEntranceTestSets()->all();
        if ($bachelorEntranceTestSets) {
            foreach ($bachelorEntranceTestSets as $set) {
                

                $set->archive();
            }
        }
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {
            $transaction = Yii::$app->db->beginTransaction();
            try {

                $errorFrom = '';
                $deleteSuccess = true;

                $agreements = $this->getAnyAgreements()->all();
                if ($agreements) {
                    foreach ($agreements as $dataToDelete) {
                        $deleteSuccess = $dataToDelete->delete();
                        if (!$deleteSuccess) {
                            $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                            break;
                        }
                    }
                }

                
                if ($deleteSuccess) {
                    
                    $bachelorEntranceTestSets = $this->getRawBachelorEntranceTestSets()->all();
                    if ($bachelorEntranceTestSets) {
                        foreach ($bachelorEntranceTestSets as $dataToDelete) {
                            

                            $deleteSuccess = $dataToDelete->delete();
                            if (!$deleteSuccess) {
                                $errorFrom .= "{$this->tableName()} -> {$dataToDelete->tableName()} -> {$dataToDelete->id}\n";
                                break;
                            }
                        }
                    }
                }

                if ($deleteSuccess) {
                    BachelorSpeciality::getDb()
                        ->createCommand("DELETE FROM [[bachelor_speciality_education_data]] WHERE bachelor_speciality_id = :id", ['id' => $this->id])
                        ->execute();
                }

                if ($deleteSuccess) {
                    $transaction->commit();
                } else {
                    return false;
                }
                return true;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        } else {
            return false;
        }
    }

    public function countPosition()
    {
        $header = ListCompetitionHeader::findOne([
            'campaign_code' => $this->application->type->campaign->referenceType->reference_id,
            'speciality_system_code' => $this->speciality->speciality_code,
            'finance_code' => $this->speciality->finance_code,
            'learnform_code' => $this->speciality->eduform_code,
        ]);

        if ($header != null) {
            $position = ListCompetitionRow::findOne(
                [
                    'competition_list_id' => $header->id,
                    'group_code' => $this->speciality->competitiveGroupRef->reference_id,
                    'user_guid' => $this->application->user->guid,
                ]
            );

            if ($position != null) {
                return Yii::t(
                    'abiturient/bachelor/application/bachelor-speciality',
                    'Числительное окончание для счётной позиции в форме "НП": `{number, ordinal} в списке`',
                    ['number' => $position->row_number]
                );
            }
        }
        return false;
    }

    public function getChanceId()
    {
        $chance = ListChanceHeader::findOne([
            'campaign_code' => $this->application->type->campaign->referenceType->reference_id,
            'speciality_code' => $this->speciality->speciality_code,
            'learnform_code' => $this->speciality->eduform_code,
        ]);
        if ($chance != null) {
            return $chance->id;
        }
        return null;
    }


    









    public function canSendByPeriod(?BachelorApplication &$actual_app, bool $has_new_agreement_entities): array
    {
        if (empty($this->speciality_id)) {
            return [false, ''];
        }

        $speciality = $this->speciality;
        $info = $this->getCampaignInfosQuery()->all();

        $now = new DateTime();
        $result = false;
        $messages = [];
        if (!$info) {
            
            $messages[] = Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'Сообщение об окончании приёма на данное НП в форме "НП": `По направлению подготовки {specialityName} прием документов окончен {date}`',
                [
                    'specialityName' => implode(', ', [
                        $speciality->directionRef->reference_name ?? '',
                        $speciality->educationSourceRef->reference_name ?? '',
                        $speciality->educationFormRef->reference_name ?? '',
                    ]),
                    'date' => null,
                ]
            );
        }

        foreach ($info as $period) {
            if ((new DateTime($period->date_final)) > $now) {
                $result = true;
            } else {
                if (!$actual_app) {
                    $actual_app = DraftsManager::getActualApplication($this->application->user, $this->application->type);
                }
                
                $result = true;
                if (!$actual_app || !$this->existsInApp($actual_app)) {
                    $result = false;
                    $messages[] = Yii::t(
                        'abiturient/bachelor/application/bachelor-speciality',
                        'Сообщение об окончании приёма на данное НП в форме "НП": `По направлению подготовки {specialityName} прием документов окончен {date}`',
                        [
                            'specialityName' => implode(', ', [
                                $speciality->directionRef->reference_name ?? '',
                                $speciality->educationSourceRef->reference_name ?? '',
                                $speciality->educationFormRef->reference_name ?? '',
                            ]),
                            'date' => $period->date_final,
                        ]
                    );
                }
            }
        }

        return [$result, implode('. ', $messages)];
    }

    






    public function existsInApp(BachelorApplication $application): bool
    {
        return $application
            ->getSpecialities()
            ->andWhere(['speciality_id' => $this->speciality_id])
            ->andFilterWhere(['admission_category_id' => $this->admission_category_id])
            ->exists();
    }

    public function getCampaignInfosQuery()
    {
        return $this->speciality->getCampaignInfosQuery()
            
            ->andFilterWhere(['admission_category.ref_key' => ArrayHelper::getValue($this, 'admissionCategory.ref_key')]);
    }

    public function isDeleteRevertable()
    {
        return $this->getCampaignInfosQuery()
            ->andWhere(['>', IndependentQueryManager::strToDateTime('date_final'), date('Y-m-d H:i:s')])
            ->exists();
    }

    public function canEdit(): bool
    {
        return $this->isDeleteRevertable();
    }

    public function canSendConsent()
    {
        return !empty($this->education) && $this->education->have_original;
    }

    public function isPriorityApply()
    {
        $eduSourceReferenceUid = $this->speciality->educationSourceRef->reference_uid ?? null;
        return isset($this->preference_id) || isset($this->target_id) || $eduSourceReferenceUid === static::getTargetReceptionBasis();
    }

    public function canAddAgreements()
    {
        $application = $this->application;
        if (!ArrayHelper::getValue($application, 'type.campaign.consents_allowed', false)) {
            return false;
        }
        $time = new DateTime();
        
        $campaign_infos = $this->getCampaignInfosQuery()->with(['periodsToSendAgreement'])->all();
        foreach ($campaign_infos as $campaign_info) {
            foreach ($campaign_info->periodsToSendAgreement as $period) {
                if (!((new DateTime($period->start)) <= $time && $time <= (new DateTime($period->end)))) {
                    return false;
                }
            }
        }
        return true;
    }

    public function checkAgreementConditions(): bool
    {
        $application = $this->application;

        
        if (!ArrayHelper::getValue($application, 'type.rawCampaign.use_common_agreements', false)) {
            return true;
        }

        return $this->isInAgreementConditions();
    }

    public function isInAgreementConditions(): bool
    {
        $conditions = $this->application->type->campaign->agreementConditions ?? [];
        $edu_source_ref_uids = ArrayHelper::getColumn($conditions, 'educationSourceRef.reference_uid');
        $current_source_ref_uid = $this->speciality->educationSourceRef->reference_uid ?? null;

        return in_array($current_source_ref_uid, $edu_source_ref_uids);
    }

    public function validateAgreementDate(int $customTime = null)
    {
        
        $agreement = $this->getAgreement()
            ->andWhere(['status' => AdmissionAgreement::STATUS_NOTVERIFIED])
            ->limit(1)
            ->one();
        $no_periods_found = true;
        $campaign_infos = [];
        if (isset($agreement)) {
            $campaign = ArrayHelper::getValue($this, 'application.type.campaign');

            if ($campaign === null) {
                return true;
            }
            $firstApplyHistory = $this->application->getFirstAppliedHistory();
            $applyDayStart = null;
            $applyDayFinal = null;
            $specApplyDayStart = null;
            $specApplyDayFinal = null;
            $spec_is_in_one_s_since = $this->sent_to_one_s_at;
            $date = new DateTime(date('Y-m-d H:i:s', $customTime ?? time()));
            if (!empty($firstApplyHistory)) {
                
                $day = date('m/d/Y', $firstApplyHistory->created_at);
                $applyDayStart = strtotime($day . ' 00:00:00');
                $applyDayFinal = strtotime($day . ' 23:59:59');
            }
            if (!empty($spec_is_in_one_s_since)) {
                
                $day = date('m/d/Y', $spec_is_in_one_s_since);
                $specApplyDayStart = strtotime($day . ' 00:00:00');
                $specApplyDayFinal = strtotime($day . ' 23:59:59');
            }
            
            $campaign_infos = $this->getCampaignInfosQuery()->with(['periodsToSendAgreement'])->all();
            foreach ($campaign_infos as $campaign_info) {
                if (!empty($campaign_info->periodsToSendAgreement)) {
                    $no_periods_found = false;
                }
                foreach ($campaign_info->periodsToSendAgreement as $period) {
                    if (!((new DateTime($period->start)) <= $date && $date <= (new DateTime($period->end)))) {
                        continue;
                    }
                    if ($period->in_day_of_sending_app_only) {
                        if (empty($applyDayStart) || empty($applyDayFinal) || !((new DateTime(date('Y-m-d H:i:s', $applyDayStart))) <= $date && $date <= (new DateTime(date('Y-m-d H:i:s', $applyDayFinal))))) {
                            continue;
                        }
                    }
                    if ($period->in_day_of_sending_speciality_only) {
                        if (empty($specApplyDayStart) || empty($specApplyDayFinal) || !((new DateTime(date('Y-m-d H:i:s', $specApplyDayStart))) <= $date && $date <= (new DateTime(date('Y-m-d H:i:s', $specApplyDayFinal))))) {
                            continue;
                        }
                    }
                    
                    return true;
                }
            }

            if ($no_periods_found) {
                $this->addError(
                    'speciality_id',
                    Yii::t(
                        'abiturient/bachelor/application/bachelor-speciality',
                        'Подсказка с ошибкой для поля "speciality_id" формы "НП": `Невозможно подать согласие на зачисление для направления подготовки вне рамок сроков подачи документов для данной приемной кампании.`'
                    )
                );
                return false;
            }
            $resultString = "";
            foreach ($campaign_infos as $campaign_info) {
                foreach ($campaign_info->periodsToSendAgreement as $period) {
                    $start = $period->start;
                    $final = $period->end;
                    $additional = $period->getAdditionalConditionsDescription();
                    $resultString .= "<strong>C</strong> {$start} <strong>По</strong> {$final} {$additional}" . PHP_EOL;
                }
            }

            $this->addError(
                'speciality_id',
                Yii::t(
                    'abiturient/bachelor/application/bachelor-speciality',
                    'Подсказка с ошибкой для поля "speciality_id" формы "НП": `Невозможно подать согласие на зачисление для направления подготовки вне рамок сроков подачи согласий на зачисление для данной приемной кампании. Вы можете подать согласие на зачисление в следующие сроки:<br>`'
                ) . nl2br($resultString)
            );
            return false;
        }
        return true;
    }

    public function updateSentAt(int $timestamp)
    {
        if (!$this->sent_to_one_s_at) {
            $this->sent_to_one_s_at = $timestamp;
            $this->save(true, ['sent_to_one_s_at']);
        }
    }

    






    public function getAvailableCategories(bool $allowBenefitCategories = true)
    {
        $categories = $this->speciality->getAvailableCategories($allowBenefitCategories);
        if ((!$categories) && $this->admissionCategory) {
            return [$this->admissionCategory];
        }

        return $categories;
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'admission_category_id' => function (BachelorSpeciality $model) {
                return $model->admissionCategory === null ? null : $model->admissionCategory->description;
            },
            'speciality_id' => function (BachelorSpeciality $model) {
                return $model->speciality === null ? null : ($model->speciality->directionRef->reference_name ?? null);
            },
            'priority',
            'is_without_entrance_tests' => function (BachelorSpeciality $model) {
                return $model->getIsWithoutEntranceTestsDescription();
            },
            'preference_id' => function (BachelorSpeciality $model) {
                return $model->preference === null ? null : $model->preference->getName();
            },
            'bachelor_olympiad_id' => function (BachelorSpeciality $model) {
                return $model->bachelorOlympiad === null ? null : $model->bachelorOlympiad->getName();
            },
            'target_reception_id' => function (BachelorSpeciality $model) {
                return $model->targetReception === null ? null : $model->targetReception->getName();
            },
            'education_id' => function (BachelorSpeciality $model) {
                $educationsData = $model->educationsData;
                $result = $educationsData ? array_map(
                    function (EducationData $educationData) {
                        return $educationData->stringify();
                    },
                    $educationsData
                ) : [];

                return implode(', ', $result);
            }
        ];
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_BACHELOR_SPECIALITY;
    }

    public function getEntityIdentifier(): ?string
    {
        if ($this->speciality === null) {
            return null;
        }

        return ($this->speciality->directionRef->reference_name ?? '') . ' ' . ($this->speciality->competitiveGroupRef->reference_name ?? '');
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

    public function getConsentFileInfo(): array
    {
        $files = [];
        $agreementType = CodeSettingsManager::GetEntityByCode('agreement_document_type_guid');

        if ($this->agreement) {
            
            if ($this->agreement->linkedFile) {
                $files[] = [
                    $this->agreement,
                    $agreementType,
                    null
                ];
            }
        } elseif ($this->agreementDecline) {
            if ($this->agreementDecline->linkedFile) {
                $files[] = [
                    $this->agreementDecline,
                    $agreementType,
                    null
                ];
            }
        }

        return $files;
    }

    public function getPaidContractFilesInfo(): array
    {
        $files = [];
        $contract = $this->getAttachedPaidContract();
        if ($contract) {
            $files[] = [
                $contract,
                ArrayHelper::getValue($contract, 'attachmentType.documentType'),
                null
            ];
        }
        return $files;
    }

    public function isFullCostRecovery(): bool
    {
        $uid = $this->speciality->educationSourceRef->reference_uid ?? null;
        return ($uid == static::getCommercialBasis());
    }

    


    public function getAttachedPaidContract()
    {
        if (!$this->isFullCostRecovery() || EmptyCheck::isEmpty($this->paid_contract_guid)) {
            return null;
        }
        return $this->getAttachments()->joinWith(['attachmentType'])
            ->andWhere(['{{%attachment_type}}.system_type' => AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY])
            ->one();
    }

    public static function getTableLink(): string
    {
        return 'bachelor_speciality_attachment';
    }

    public static function getEntityTableLinkAttribute(): string
    {
        return 'bachelor_speciality_id';
    }

    public static function getAttachmentTableLinkAttribute(): string
    {
        return 'attachment_id';
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
        return AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_FULL_RECOVERY_SPECIALITY);
    }

    public static function getApplicationIdColumn(): string
    {
        return 'application_id';
    }

    public function getName(): string
    {
        $return_str = Yii::t(
            'abiturient/bachelor/application/bachelor-speciality',
            'Префикс имени НП в формы "НП": `Направление подготовки`'
        );
        if (!empty($this->speciality)) {
            $return_str .= " {$this->speciality->speciality_name}";
        }
        return $return_str;
    }

    public function stringify(): string
    {
        return $this->getName();
    }

    public function getAttachmentCollection(): FileToShowInterface
    {
        return new AttachedEntityAttachmentCollection(
            $this->application->user,
            $this,
            $this->getAttachmentType(),
            array_filter([$this->getAttachedPaidContract()]),
            $this->formName(),
            'file'
        );
    }

    public function getAttachmentConnectors(): array
    {
        return ['application_id' => $this->application->id];
    }

    public function getUserInstance(): User
    {
        return ArrayHelper::getValue($this, 'application.user') ?: new User();
    }

    








    public function buildAndUpdateContractRefFor1C(?array $contracts = null): ?array
    {
        $ret = null;
        if (is_null($contracts)) {
            $soap_response = $this->application->GetAbitContractListResponse();
            $contracts = ArrayHelper::getValue(ToAssocCaster::getAssoc($soap_response), 'return.Contract', []);
        }
        if (!empty($contracts)) {
            if (!is_array($contracts) || ArrayHelper::isAssociative($contracts)) {
                $contracts = [$contracts];
            }
            foreach ($contracts as $contract) {
                if (isset($contract['idApplicationString']) && !EmptyCheck::isEmpty((string)$contract['idApplicationString'])) {
                    $idApplicationString = (string)$contract['idApplicationString'];
                    if ($this->application_code == $idApplicationString) {
                        $ret = $contract['ContractRef'];
                        $this->paid_contract_guid = $contract['ContractRef']['ReferenceUID'];
                        if (!$this->save()) {
                            throw new RecordNotValid($this);
                        }
                        break;
                    }
                }
            }
        }
        if (!$ret) {
            throw new ServerErrorHttpException('Не удалось получить информацию о договоре');
        }
        return $ret;
    }

    public function getAdmissionCategory()
    {
        return $this->hasOne(AdmissionCategory::class, ['id' => 'admission_category_id']);
    }

    


    public function getIsWithoutEntranceTests()
    {
        return boolval($this->is_without_entrance_tests);
    }

    public function getIsWithoutEntranceTestsDescription(): string
    {
        $message = $this->getIsWithoutEntranceTests()
            ? 'Подпись наличия признака без вступительных испытаний на направление подготовки: `да`'
            : 'Подпись отсутствия признака без вступительных испытаний на направление подготовки: `нет`';
        return Yii::t('abiturient/bachelor/application/bachelor-speciality', $message);
    }

    


    public function getEgeResults()
    {
        $query = $this->getEgeResultsByEntranceTestParamsOnly();

        $tnEgeResult = EgeResult::tableName();
        return $query->andOnCondition(["{$tnEgeResult}.application_id" => $this->application_id]);
    }

    


    public function getEgeResultsByEntranceTestParamsOnly()
    {
        return $this->hasMany(EgeResult::class, ['entrance_test_junction' => 'entrance_test_junction'])
            ->via('bachelorEntranceTestSets');
    }

    





    public function getConfirmedEge(
        StoredDisciplineReferenceType $subjectRef,
        $childrenSubjectRef = null
    ) {
        $tnEgeResult = EgeResult::tableName();
        $tnDisciplineRefType = StoredDisciplineReferenceType::tableName();
        $tnChildDisciplineRefType = StoredChildDisciplineReferenceType::tableAliasForJoin();

        $query = $this->getEgeResults()
            ->joinWith('cgetDiscipline')
            ->joinWith("cgetChildDiscipline {$tnChildDisciplineRefType}")
            ->joinWith('bachelorEntranceTestSetsByEntranceTestParamsOnly')
            ->andWhere([
                "{$tnEgeResult}.application_id"        => $this->application_id,
                "{$tnDisciplineRefType}.reference_uid" => $subjectRef->reference_uid,
            ]);
        if ($childrenSubjectRef) {
            $query = $query->andWhere([
                "{$tnChildDisciplineRefType}.reference_uid" => $childrenSubjectRef->reference_uid
            ]);
        }

        return $query;
    }

    







    public function isSubjectInConfirmedEntranceTestsSet(
        StoredDisciplineReferenceType $subjectRef,
        $childrenSubjectRef = null
    ): bool {
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();
        return $this->getConfirmedEge($subjectRef, $childrenSubjectRef)
            ->joinWith('bachelorEntranceTestSetsByEntranceTestParamsOnly')
            ->andWhere(["{$tnBachelorEntranceTestSet}.bachelor_speciality_id" => $this->id])
            ->exists();
    }

    


    private function checkEntrantSetArchive(): bool
    {
        $value = ArrayHelper::getValue($this, 'cgetEntranceTestSet.archive');

        return (bool)$value;
    }

    








    public function getEntrantTestFormByDiscipline(
        StoredDisciplineReferenceType $subjectRef,
        $childrenSubjectRef = null
    ): array {
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();
        
        $entrantTests = $this->getConfirmedEge($subjectRef, $childrenSubjectRef)
            ->joinWith('bachelorEntranceTestSetsByEntranceTestParamsOnly')
            ->andWhere(["{$tnBachelorEntranceTestSet}.bachelor_speciality_id" => $this->id])
            ->all();

        if (!$entrantTests) {
            $speciality_name = $this->getSpecialityNameForException();
            throw new UserException("Ошибка при получении выбранной формы сдачи ВИ. В справочниках отсутствует информация о предмете \"{$subjectRef->reference_name}\" в наборе ВИ для направления подготовки {$speciality_name}.");
        }

        $I = 0;
        
        $formReferenceTypes = [];
        foreach ($entrantTests as $entrantTests) {
            

            $formReferenceType = $entrantTests->getCgetExamForm()->one();

            if (is_null($formReferenceType)) {
                $speciality_name = $this->getSpecialityNameForException();
                throw new UserException("Ошибка при получении выбранной формы сдачи ВИ. В справочниках отсутствует информация о форме сдачи для предмета \"{$subjectRef->reference_name}\" в наборе ВИ для направления подготовки {$speciality_name}.");
            }

            $index = "editable_{$I}";
            if ($entrantTests->readonly) {
                $index = "readonly_{$I}";
            }
            $formReferenceTypes[$index] = $formReferenceType;
            $I++;
        }

        return $formReferenceTypes;
    }

    




    private function getSpecialityNameForException()
    {
        $specialitySpecialityName = ArrayHelper::getValue(
            $this,
            'speciality.directionRef.reference_name',
            Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'пустое значение для "наименование направления" при формировании текста ошибки в формы "НП": `(Наименование направления не задано)`'
            )
        );
        $specialityEduformName = ArrayHelper::getValue(
            $this,
            'speciality.educationFormRef.reference_name',
            Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'пустое значение для "форма обучения" при формировании текста ошибки в формы "НП": `(Форма обучения не задана)`'
            )
        );
        $specialityGroupName = ArrayHelper::getValue(
            $this,
            'speciality.competitiveGroupRef.reference_name',
            Yii::t(
                'abiturient/bachelor/application/bachelor-speciality',
                'пустое значение для "наименование конкурсной" при формировании текста ошибки в формы "НП": `(Наименование конкурсной группы не задано)`'
            )
        );
        return implode(
            ', ',
            [
                $specialitySpecialityName,
                $specialityEduformName,
                $specialityGroupName,
            ]
        );
    }

    




    public function isEntrantTestSetConfirmed(): bool
    {
        $egeResultTableName = EgeResult::tableName();
        $bachelorEntranceTestSetTableName = BachelorEntranceTestSet::tableName();
        return BachelorEntranceTestSet::find()
            ->joinWith('egeResultByEntranceTestParamsOnly')
            ->andWhere([
                "{$egeResultTableName}.application_id" => $this->application_id,
                "{$bachelorEntranceTestSetTableName}.bachelor_speciality_id" => $this->id,
            ])
            ->exists();
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    


    public function isCommercialBasis(): bool
    {
        $uid = ArrayHelper::getValue($this, 'speciality.educationSourceRef.reference_uid');
        return $uid === BachelorSpeciality::getCommercialBasis();
    }

    


    public function validateTargetExists()
    {
        $error = static::getMissingTargetReceptionError();
        if (is_null($this->targetReception)) {
            $this->addError('target_reception_id', $error);
        } elseif ($this->targetReception->archive) {
            $this->addError('target_reception_id', $error);
        }
    }

    


    public function validatePreferenceExists()
    {
        $error = static::getMissingPreferenceError();
        if (is_null($this->preference)) {
            $this->addError('preference_id', $error);
        } elseif ($this->preference->archive) {
            $this->addError('preference_id', $error);
        }
    }

    public function getRelationsInfo(): array
    {
        return [
            new OneToManyRelationPresenter('admissionAgreements', [
                'parent_instance' => $this,
                'child_class' => AdmissionAgreement::class,
                'child_column_name' => 'speciality_id',
            ]),
            new AttachmentsRelationPresenter('attachments', [
                'parent_instance' => $this,
            ]),
            new ManyToManyRelationPresenter('educationsData', [
                'parent_instance' => $this,
                'parent_column_name' => 'id',

                'child_class' => EducationData::class,
                'child_column_name' => 'id',

                'via_table' => BachelorSpeciality::getBachelorSpecialityEducationDataTable(),
                'via_table_parent_column' => 'bachelor_speciality_id',
                'via_table_child_column' => 'education_data_id',

                'make_new_child' => false,
                'get_possible_children_callback' => function (BachelorSpeciality $speciality) {
                    
                    $app = $speciality->application;
                    return $app->rawEducations;
                }
            ]),
            new OneToOneRelationPresenter('preference', [
                'parent_instance' => $this,
                'child_class' => BachelorPreferences::class,
                'child_column_name' => 'id',
                'parent_column_name' => 'preference_id',
                'make_new_child' => false,
                'get_possible_children_callback' => function (BachelorSpeciality $speciality) {
                    
                    $app = $speciality->application;
                    return $app->rawBachelorPreferencesSpecialRight;
                }
            ]),
            new OneToOneRelationPresenter('olympiad', [
                'parent_instance' => $this,
                'child_class' => BachelorPreferences::class,
                'child_column_name' => 'id',
                'parent_column_name' => 'bachelor_olympiad_id',
                'make_new_child' => false,
                'get_possible_children_callback' => function (BachelorSpeciality $speciality) {
                    
                    $app = $speciality->application;
                    return $app->rawBachelorPreferencesOlymp;
                }
            ]),
            new OneToOneRelationPresenter('targetReception', [
                'parent_instance' => $this,
                'child_class' => BachelorTargetReception::class,
                'child_column_name' => 'id',
                'parent_column_name' => 'target_reception_id',
                'make_new_child' => false,
                'get_possible_children_callback' => function (BachelorSpeciality $speciality) {
                    
                    $app = $speciality->application;
                    return $app->rawTargetReceptions;
                }
            ]),
            new OneToManyRelationPresenter('bachelorEntranceTestSets', [
                'parent_instance' => $this,
                'child_class' => BachelorEntranceTestSet::class,
                'find_exists_child' => false,
                'child_column_name' => 'bachelor_speciality_id',
                'ignore_in_comparison' => true,
            ]),
            new OneToOneRelationPresenter('specialityPriority', [
                'parent_instance' => $this,
                'child_class' => SpecialityPriority::class,
                'child_column_name' => 'bachelor_speciality_id',
            ]),
        ];
    }

    public function getSpecialityString()
    {
        $speciality_campaign_id = ArrayHelper::getValue($this, 'speciality.campaignRef.reference_id', '');
        $speciality_competitive_group_id = ArrayHelper::getValue($this, 'speciality.competitiveGroupRef.reference_id', '');
        $speciality_curriculum_id = ArrayHelper::getValue($this, 'speciality.curriculumRef.reference_id', '');
        $speciality_education_level_id = ArrayHelper::getValue($this, 'speciality.educationLevelRef.reference_id', '');
        $speciality_subdivision_id = ArrayHelper::getValue($this, 'speciality.subdivisionRef.reference_id', '');
        $speciality_detail_group_code = ArrayHelper::getValue($this, 'speciality.detailGroupRef.reference_id', '');
        return "{$speciality_campaign_id}_{$speciality_competitive_group_id}_{$speciality_curriculum_id}_{$speciality_education_level_id}_{$speciality_subdivision_id}_{$speciality_detail_group_code}";
    }

    public function getAdmissionCategoryName()
    {
        return ArrayHelper::getValue($this, 'admissionCategory.description');
    }

    public function getAgreementRecords()
    {
        return $this->hasMany(AgreementRecord::class, [
            'speciality_guid' => 'application_code',
            'application_id' => 'application_id',
        ])
            ->orderBy([AgreementRecord::tableName() . '.date' => SORT_ASC]);
    }

    public function getAgreementStateString()
    {
        return $this->agreement ? Yii::t(
            'abiturient/bachelor/application/bachelor-speciality',
            'Подпись наличия согласия на зачисление; формы "НП": `есть`'
        ) : Yii::t(
            'abiturient/bachelor/application/bachelor-speciality',
            'Подпись отсутствия согласия на зачисление; формы "НП": `нет`'
        );
    }

    public function getAgreementToDeleteStateString()
    {
        return $this->agreementDecline ? Yii::t(
            'abiturient/bachelor/application/bachelor-speciality',
            'Подпись наличия отзыва согласия на зачисление; формы "НП": `есть`'
        ) : Yii::t(
            'abiturient/bachelor/application/bachelor-speciality',
            'Подпись отсутствия отзыва согласия на зачисление; формы "НП": `нет`'
        );
    }

    public function getPreferenceDescription()
    {
        return ArrayHelper::getValue($this, 'preference.description');
    }

    public function getBachelorOlympiadDescription()
    {
        return ArrayHelper::getValue($this, 'bachelorOlympiad.description');
    }

    public function getTargetReceptionDescription()
    {
        return ArrayHelper::getValue($this, 'targetReception.name_company');
    }

    


    public function getEducationDescription()
    {
        return ArrayHelper::getValue($this, 'education.descriptionString');
    }

    public function getIdentityString(): string
    {
        $speciality_campaign_uid = ArrayHelper::getValue($this, 'speciality.campaignRef.reference_uid', '');
        $speciality_competitive_group_uid = ArrayHelper::getValue($this, 'speciality.competitiveGroupRef.reference_uid', '');
        $speciality_curriculum_uid = ArrayHelper::getValue($this, 'speciality.curriculumRef.reference_uid', '');
        $speciality_education_level_uid = ArrayHelper::getValue($this, 'speciality.educationLevelRef.reference_uid', '');
        return "{$speciality_campaign_uid}_{$speciality_competitive_group_uid}_{$speciality_curriculum_uid}_{$speciality_education_level_uid}_{$this->speciality_id}";
    }

    public function getPropsToCompare(): array
    {
        return [
            'specialityString',
            'admissionCategoryName',
            'isWithoutEntranceTestsDescription',
        ];
    }

    public function getAttachedFilesInfo(): array
    {
        return [
            ...$this->getConsentFileInfo(),
            ...$this->getPaidContractFilesInfo()
        ];
    }

    public function attachFile(IReceivedFile $receivingFile, DocumentType $documentType): ?File
    {
        $agreementDocumentType = CodeSettingsManager::GetEntityByCode('agreement_document_type_guid');
        if ($agreementDocumentType->ref_key == $documentType->ref_key) {
            if ($this->agreement) {
                $stored_file = $receivingFile->getFile($this->agreement);

                $this->agreement->LinkFile($stored_file);
                return $stored_file;
            } else {
                
                $any_state_agreement = $this->getRawAgreements()->one();
                if ($any_state_agreement && $any_state_agreement->status == AdmissionAgreement::STATUS_MARKED_TO_DELETE) {
                    $agreementDecline = $this->agreementDecline;
                    if (!$agreementDecline) {
                        $agreementDecline = new AgreementDecline();
                        $agreementDecline->agreement_id = $any_state_agreement->id;
                    }
                    $agreementDecline->archive = false;
                    $agreementDecline->save(false);
                    $stored_file = $receivingFile->getFile($agreementDecline);

                    $agreementDecline->LinkFile($stored_file);
                    return $stored_file;
                }
            }
        } else {
            $paid_contract_attachment_type = $this->getAttachmentType();
            if (!$paid_contract_attachment_type->documentType) {
                throw new UserException("Для договора об оказании платных образовательных услуг не установлен тип документа");
            }
            if ($paid_contract_attachment_type->documentType->ref_key == $documentType->ref_key) {
                $a = AttachmentManager::AttachFileToLinkableEntity($this, $receivingFile);
                return $a->linkedFile;
            }
        }
        return null;
    }

    public function removeNotPassedFiles(array $file_ids_to_ignore)
    {
        

        
        $ignored_attachment_ids = $this->getAttachments()
            ->select(['MAX(attachment.id) id'])
            ->joinWith(['linkedFile linked_file'])
            ->joinWith('attachmentType')
            ->andWhere(['linked_file.id' => $file_ids_to_ignore])
            ->groupBy(['linked_file.id', 'attachment_type.id']);

        
        $attachments_to_delete = $this->getAttachments()
            ->joinWith(['linkedFile linked_file'])
            ->andWhere(['not', ['attachment.id' => $ignored_attachment_ids]])
            ->all();
        foreach ($attachments_to_delete as $attachment_to_delete) {
            $attachment_to_delete->silenceSafeDelete();
        }
    }

    public function getIgnoredOnCopyingAttributes(): array
    {
        return [
            ...DraftsManager::$attributes_to_ignore,
            'application_id',
        ];
    }

    




    public function getEducationsRefAttributeUidByPath(string $path): array
    {
        $refs = [];
        foreach ($this->educationsData as $education) {
            

            $tmpRef = ReferenceTypeManager::GetReference($education, $path);
            if (ReferenceTypeManager::isReferenceTypeEmpty($tmpRef)) {
                continue;
            }
            $refs[] = ArrayHelper::getValue($tmpRef, 'ReferenceUID');
        }

        return $refs;
    }

    public function buildSpecialityArrayForEnrollmentRejection(): array
    {
        return [
            'ApplicationStringGUID' => $this->application_code,
            'CompetitiveGroupRef' => ReferenceTypeManager::GetReference($this->speciality, 'competitiveGroupRef'),
            'DirectionRef' => ReferenceTypeManager::GetReference($this->speciality, 'directionRef'),
            'CurriculumRef' => ReferenceTypeManager::GetReference($this->speciality, 'curriculumRef'),
            'EducationLevelRef' => ReferenceTypeManager::GetReference($this->speciality, 'educationLevelRef'),
            'EducationFormRef' => ReferenceTypeManager::GetReference($this->speciality, 'educationFormRef'),
            'EducationProgramRef' => ReferenceTypeManager::GetReference($this->speciality, 'educationProgramRef'),
            'EducationSourceRef' => ReferenceTypeManager::GetReference($this->speciality, 'educationSourceRef'),
            'LevelBudgetRef' => ReferenceTypeManager::GetReference($this->speciality, 'budgetLevelRef'),
            'UGSRef' => ReferenceTypeManager::GetReference($this->speciality, 'ugsRef'),
            'BranchRef' => ReferenceTypeManager::GetReference($this->speciality, 'branchRef'),
        ];
    }

    public function getEnrollmentRejectionAttachments(): ActiveQuery
    {
        return $this->getAttachments()
            ->joinWith('attachmentType attachment_type_table', false)
            ->andOnCondition([
                Attachment::tableName() . '.deleted' => false,
                'attachment_type_table.system_type' => AttachmentType::SYSTEM_TYPE_ENROLLMENT_REJECTION
            ]);
    }

    public function getEnrollmentRejectionAttachmentCollection(): FileToShowInterface
    {
        return new AttachedEntityAttachmentCollection(
            $this->application->user,
            $this,
            $this->getEnrollmentRejectionAttachmentType(),
            array_filter($this->getEnrollmentRejectionAttachments()->all()),
            $this->formName(),
            'file'
        );
    }

    public function getEnrollmentRejectionAttachmentType(): AttachmentType
    {
        return AttachmentManager::GetSystemAttachmentType(AttachmentType::SYSTEM_TYPE_ENROLLMENT_REJECTION);
    }

    public function getEnrollmentRejectionFilesInfo(): array
    {
        $files = [];
        $doc_type = CodeSettingsManager::GetEntityByCode('enrollment_rejection_doc_type_guid');

        foreach ($this->enrollmentRejectionAttachments as $attachment) {
            $files[] = [
                $attachment,
                $doc_type,
                null
            ];
        }

        return $files;
    }
}
