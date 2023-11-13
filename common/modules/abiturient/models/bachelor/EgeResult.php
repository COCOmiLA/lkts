<?php

namespace common\modules\abiturient\models\bachelor;

use common\components\AfterValidateHandler\LoggingAfterValidateHandler;
use common\components\EntrantTestManager\CentralizedTestingManager;
use common\components\EntrantTestManager\EntrantTestManager;
use common\components\EntrantTestManager\ExamsScheduleManager;
use common\components\EntrantTestManager\JointEntrantTestManager;
use common\components\queries\ArchiveQuery;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DictionaryDateTimeOfExamsSchedule;
use common\models\dictionary\DictionaryPredmetOfExamsSchedule;
use common\models\dictionary\DictionaryReasonForExam;
use common\models\dictionary\ForeignLanguage;
use common\models\dictionary\StoredReferenceType\SpecialRequirementReferenceType;
use common\models\dictionary\StoredReferenceType\StoredChildDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\interfaces\ArchiveModelInterface;
use common\models\relation_presenters\comparison\interfaces\ICanGivePropsToCompare;
use common\models\relation_presenters\comparison\interfaces\IHaveIdentityProp;
use common\models\relation_presenters\OneToOneRelationPresenter;
use common\models\traits\ArchiveTrait;
use common\models\traits\HasDirtyAttributesTrait;
use common\models\traits\HtmlPropsEncoder;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistory;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryClasses;
use common\modules\abiturient\models\bachelor\changeHistory\ChangeHistoryDecoratedModel;
use common\modules\abiturient\models\bachelor\changeHistory\interfaces\ChangeLoggedModelInterface;
use common\modules\abiturient\models\drafts\IHasRelations;
use common\modules\abiturient\models\interfaces\ApplicationConnectedInterface;
use common\modules\abiturient\models\interfaces\ExamInterface;
use common\modules\abiturient\models\interfaces\ICanBeStringified;
use common\modules\abiturient\validators\CompetitiveGroupEntranceTestsValidator;
use common\modules\abiturient\validators\EgeResultValidator;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;















































final class EgeResult extends ChangeHistoryDecoratedModel implements
    ExamInterface,
    ApplicationConnectedInterface,
    ChangeLoggedModelInterface,
    IHaveIdentityProp,
    ICanGivePropsToCompare,
    IHasRelations,
    ArchiveModelInterface,
    ICanBeStringified
{
    use ArchiveTrait;
    use HtmlPropsEncoder;
    use HasDirtyAttributesTrait;

    
    private $_minimalMaximalScore;

    
    public $_application = null;

    
    public $priority;

    
    public $index = '';

    public const SCENARIO_SAVE_SETTINGS = 'save_settings';

    public static function tableName()
    {
        return '{{%bachelor_egeresult}}';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->_oldAttributes = $this->attributes;
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[EgeResult::SCENARIO_SAVE_SETTINGS] = $scenarios[EgeResult::SCENARIO_DEFAULT];
        return $scenarios;
    }

    


    public function rules()
    {
        return [
            [
                'exam_form',
                'safe'
            ],
            [
                [
                    'status',
                    'language_id',
                    'archived_at',
                    'discipline_id',
                    'application_id',
                    'cget_exam_form_id',
                    'reason_for_exam_id',
                    'cget_discipline_id',
                    'child_discipline_id',
                    'cget_child_discipline_id',
                    'special_requirement_ref_id',
                ],
                'integer'
            ],
            [
                [
                    'archive',
                    'readonly',
                ],
                'boolean',
            ],
            [
                [
                    'archive',
                    'readonly',
                ],
                'default',
                'value' => false
            ],
            [
                'status',
                'default',
                'value' => EgeResult::STATUS_UNSTAGED
            ],
            [
                ['status'],
                'in',
                'range' => [
                    EgeResult::STATUS_STAGED,
                    EgeResult::STATUS_VERIFIED,
                    EgeResult::STATUS_UNSTAGED,
                    EgeResult::STATUS_NOTVERIFIED,
                ]
            ],
            [
                ['readonly'],
                'in',
                'range' => [false, true]
            ],
            [
                [
                    'discipline_points',
                    'egeyear',
                    'exam_form',
                    'child_discipline_code'
                ],
                'string',
                'max' => 100
            ],
            [
                'entrance_test_junction',
                'string',
                'max' => 32
            ],
            [
                [
                    'cget_discipline_id',
                    'cget_child_discipline_id',
                ],
                CompetitiveGroupEntranceTestsValidator::class,
            ],
            [
                'cget_child_discipline_id',
                'required',
                'when' => function (EgeResult $model) {
                    return $model->hasChildren();
                },
                'message' => Yii::t(
                    'abiturient/bachelor/ege/ege-result',
                    'Подсказка с ошибкой для поля "cget_child_discipline_id"; формы ВИ: `Укажите предмет вступительного испытания`'
                ),
                'on' => [self::SCENARIO_SAVE_SETTINGS]
            ],
            [
                'cget_exam_form_id',
                'required'
            ],
            [
                ['reason_for_exam_id'],
                'exist',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'targetClass' => DictionaryReasonForExam::class,
                'targetAttribute' => ['reason_for_exam_id' => 'id']
            ],
            [
                ['language_id'],
                'exist',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'targetClass' => ForeignLanguage::class,
                'targetAttribute' => ['language_id' => 'id']
            ],
            [
                ['cget_exam_form_id'],
                'exist',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'targetClass' => StoredDisciplineFormReferenceType::class,
                'targetAttribute' => ['cget_exam_form_id' => 'id']
            ],
            [
                ['cget_discipline_id'],
                'exist',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'targetClass' => StoredDisciplineReferenceType::class,
                'targetAttribute' => ['cget_discipline_id' => 'id']
            ],
            [
                ['cget_child_discipline_id'],
                'exist',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'targetClass' => StoredChildDisciplineReferenceType::class,
                'targetAttribute' => ['cget_child_discipline_id' => 'id']
            ],
            [
                ['special_requirement_ref_id'],
                'exist',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'targetClass' => SpecialRequirementReferenceType::class,
                'targetAttribute' => ['special_requirement_ref_id' => 'id']
            ],
            [
                'discipline_points',
                EgeResultValidator::class,
            ],
        ];
    }

    public function getReasonForExam()
    {
        return $this->hasOne(DictionaryReasonForExam::class, ['id' => 'reason_for_exam_id']);
    }

    


    public function getRawBachelorResultCentralizedTesting()
    {
        return $this->hasOne(BachelorResultCentralizedTesting::class, ['egeresult_id' => 'id']);
    }

    


    public function getBachelorResultCentralizedTesting()
    {
        return $this->getRawBachelorResultCentralizedTesting()
            ->active();
    }

    




    public function getOrBuildCentralizedTesting(bool $isCreatedByUser = true): BachelorResultCentralizedTesting
    {
        $centralizedTesting = $this->bachelorResultCentralizedTesting;
        if (!$centralizedTesting) {
            $centralizedTesting = new BachelorResultCentralizedTesting();
            $centralizedTesting->egeresult_id = $this->id;
            $centralizedTesting->isNew = true;
        }

        if ($isCreatedByUser) {
            $centralizedTesting->scenario = BachelorResultCentralizedTesting::SCENARIO_CREATED_BY_USER;
        }

        return $centralizedTesting;
    }

    public function getLanguage()
    {
        return $this->hasOne(ForeignLanguage::class, ['id' => 'language_id']);
    }

    


    public function getRawBachelorDatePassingEntranceTest()
    {
        $tn = BachelorDatePassingEntranceTest::tablename();
        return $this->hasOne(BachelorDatePassingEntranceTest::class, ['bachelor_egeresult_id' => 'id'])
            ->andWhere(['IS', "{$tn}.parent_id", null]);
    }

    


    public function getAllRawBachelorDatePassingEntranceTest()
    {
        return $this->hasMany(BachelorDatePassingEntranceTest::class, ['bachelor_egeresult_id' => 'id']);
    }

    


    public function getAllBachelorDatePassingEntranceTest()
    {
        return $this->hasMany(BachelorDatePassingEntranceTest::class, ['bachelor_egeresult_id' => 'id'])
            ->active();
    }

    


    public function getBachelorDatePassingEntranceTest()
    {
        return $this->getRawBachelorDatePassingEntranceTest()
            ->active();
    }

    




    public function getOrCreateBachelorDatePassingEntranceTest()
    {
        $tn = BachelorDatePassingEntranceTest::tablename();
        $result = $this->getBachelorDatePassingEntranceTest()
            ->andWhere(['IS', "{$tn}.parent_id", null])
            ->one();
        if (empty($result)) {
            $result = new BachelorDatePassingEntranceTest();
            $result->from_1c = false;
            if (!empty($this->id)) {
                $result->bachelor_egeresult_id = $this->id;
            }
        }

        return $result;
    }

    public function getSpecialRequirement()
    {
        return $this->hasOne(SpecialRequirementReferenceType::class, ['id' => 'special_requirement_ref_id']);
    }

    


    public function attributeLabels()
    {
        return [
            'status' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "status"; формы ВИ: `Проверено`'),
            'egeyear' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "egeyear"; формы ВИ: `Год`'),
            'exam_form' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "exam_form"; формы ВИ: `Форма сдачи`'),
            'egeyear_id' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "egeyear_id"; формы ВИ: `Заявление`'),
            'language_id' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "language_id"; формы ВИ: `Язык`'),
            'languageName' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "languageName"; формы ВИ: `Язык`'),
            'discipline_id' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "discipline_id"; формы ВИ: `Предмет`'),
            'cgetExamFormName' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "cget_exam_form_id"; формы ВИ: `Форма сдачи`'),
            'cget_exam_form_id' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "cget_exam_form_id"; формы ВИ: `Форма сдачи`'),
            'discipline_points' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "discipline_points"; формы ВИ: `Балл`'),
            'reasonForExamName' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "reasonForExamName" ; формы ВИ: `Основание`'),
            'cgetDisciplineName' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "cgetDisciplineName"; формы ВИ: `Предмет`'),
            'cget_discipline_id' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "cget_discipline_id"; формы ВИ: `Предмет`'),
            'reason_for_exam_id' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "reason_for_exam_id"; формы ВИ: `Основание`'),
            'specialRequirementName' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "specialRequirementName"; формы ВИ: `Специальное условие`'),
            'cgetChildDisciplineName' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "cgetChildDisciplineName"; формы ВИ: `Предмет`'),
            'cget_child_discipline_id' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "cget_child_discipline_id"; формы ВИ: `Предмет`'),
            'special_requirement_ref_id' => Yii::t('abiturient/bachelor/ege/ege-result', 'Подпись для поля "special_requirement_ref_id"; формы ВИ: `Специальное условие`'),
        ];
    }

    public function getApplication()
    {
        return $this->hasOne(BachelorApplication::class, ['id' => 'application_id']);
    }

    


    public function getBachelorEntranceTestSets()
    {
        return $this->getRawBachelorEntranceTestSets()
            ->active();
    }

    


    public function getRawBachelorEntranceTestSets()
    {
        $query = $this->getRawBachelorEntranceTestSetsByEntranceTestParamsOnly();

        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();
        $subQuery = BachelorSpeciality::find()
            ->select("{$tnBachelorSpeciality}.id")
            ->andWhere(["{$tnBachelorSpeciality}.application_id" => $this->application_id]);

        return $query->andOnCondition(['IN', "{$tnBachelorEntranceTestSet}.bachelor_speciality_id", $subQuery]);
    }

    


    public function getBachelorEntranceTestSetsByEntranceTestParamsOnly()
    {
        return $this->getRawBachelorEntranceTestSetsByEntranceTestParamsOnly()
            ->active();
    }

    


    public function getRawBachelorEntranceTestSetsByEntranceTestParamsOnly()
    {
        return $this->hasMany(BachelorEntranceTestSet::class, ['entrance_test_junction' => 'entrance_test_junction']);
    }

    


    public function getBachelorSpecialities()
    {
        return $this->hasMany(BachelorSpeciality::class, ['id' => 'bachelor_speciality_id'])
            ->via('bachelorEntranceTestSets');
    }

    


    public function getRawBachelorSpecialities()
    {
        return $this->hasMany(BachelorSpeciality::class, ['id' => 'bachelor_speciality_id'])
            ->via('rawBachelorEntranceTestSets');
    }

    public function getBachelorSpecialitiesWithEagerLoad()
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        return $this->getBachelorSpecialities()
            ->with(EgeResult::joinListForSpecialty())
            ->andWhere(["{$tnBachelorSpeciality}.application_id" => $this->application_id]);
    }

    public function getRawBachelorSpecialitiesWithEagerLoad()
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        return $this->getRawBachelorSpecialities()
            ->with(EgeResult::joinListForSpecialty())
            ->andWhere(["{$tnBachelorSpeciality}.application_id" => $this->application_id]);
    }

    


    private static function joinListForSpecialty(): array
    {
        return [
            'admissionCategory',
            'speciality',
            'speciality.campaignRef',
            'speciality.competitiveGroupRef',
            'speciality.curriculumRef',
            'speciality.educationLevelRef',
            'speciality.subdivisionRef',
        ];
    }

    public function getCgetExamForm()
    {
        return $this->hasOne(StoredDisciplineFormReferenceType::class, ['id' => 'cget_exam_form_id']);
    }

    public function getCgetDiscipline()
    {
        return $this->hasOne(StoredDisciplineReferenceType::class, ['id' => 'cget_discipline_id']);
    }

    public function getCgetChildDiscipline()
    {
        return $this->hasOne(StoredChildDisciplineReferenceType::class, ['id' => 'cget_child_discipline_id']);
    }

    public function getCgetEntranceTest()
    {
        return $this->hasOne(CgetEntranceTest::class, [
            'subject_ref_id' => 'cget_discipline_id',
            'entrance_test_result_source_ref_id' => 'cget_exam_form_id',
        ]);
    }

    public function getDisciplineRef()
    {
        if ($this->hasChildren()) {
            return ReferenceTypeManager::GetReference($this->cgetChildDiscipline);
        } else {
            return ReferenceTypeManager::GetReference($this->cgetDiscipline);
        }
    }

    public function getDisciplineCode()
    {
        if ($this->hasChildren()) {
            return $this->cgetChildDiscipline->reference_id;
        } else {
            return $this->cgetDiscipline->reference_id;
        }
    }

    public function getClassTypeForChangeHistory(): int
    {
        return ChangeHistoryClasses::CLASS_EXAM_RESULT;
    }

    public function getDisciplineReferenceName(): string
    {
        $discipline = ArrayHelper::getValue($this, 'cgetDiscipline.reference_name');
        if ($this->cget_child_discipline_id) {
            $childDiscipline = ArrayHelper::getValue($this, 'cgetChildDiscipline.reference_name');
            $discipline .= " ({$childDiscipline})";
        }

        return $discipline;
    }

    public function getChangeLoggedAttributes()
    {
        return [
            'discipline_id' => function ($model) {
                return $model->disciplineReferenceName;
            },
            'egeyear',
            'exam_form' => function ($model) {
                return ArrayHelper::getValue($model, 'cgetExamForm.reference_name');
            },
            'discipline_points',
            'reason_for_exam_id' => function ($model) {
                return ArrayHelper::getValue($model, 'reasonForExam.name');
            },
            'language_id' => function ($model) {
                return ArrayHelper::getValue($model, 'language.description');
            },
        ];
    }

    public function getReasonForExamName()
    {
        return ArrayHelper::getValue($this, 'reasonForExam.name');
    }

    public function getOldClass(): ChangeLoggedModelInterface
    {
        $class = new EgeResult();
        $class->attributes = $this->_oldAttributes;
        return $class;
    }

    public function getEntityIdentifier(): ?string
    {
        return ArrayHelper::getValue($this, 'disciplineRef.ReferenceName');
    }

    public function getEntityChangeType(): int
    {
        return ChangeHistory::CHANGE_HISTORY_EXAM_POINTS;
    }

    public function isEgeChosen()
    {
        return isset($this->cgetExamForm) && $this->cgetExamForm->reference_uid == Yii::$app->configurationManager->getCode('discipline_ege_form');
    }

    




    private static function getRelatedTestSets(BachelorApplication $application): array
    {
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();
        $selectQuery = new Expression("CONCAT(
            {$tnBachelorEntranceTestSet}.bachelor_speciality_id,
            '__',
            {$tnBachelorEntranceTestSet}.entrance_test_junction
        )");
        return BachelorEntranceTestSet::find()
            ->select($selectQuery)
            ->andWhere([
                'IN', 'entrance_test_junction', $application
                    ->getEgeResults()
                    ->select('entrance_test_junction')
            ])
            ->andWhere([
                'IN', 'bachelor_speciality_id', $application
                    ->getSpecialitiesWithoutOrdering()
                    ->select('id')
            ])
            ->column();
    }

    





    public static function loadFromPost(BachelorApplication $application, array $post = []): bool
    {
        $egeAttributesNotToArchive = [];
        $testSetNotToArchiveId = [];
        $selfFormName = (new EgeResult())->formName();
        $any_set_created = false;

        $beforeLoad = EgeResult::getRelatedTestSets($application);

        
        $posData = ArrayHelper::getValue($post, $selfFormName);
        if (!empty($posData)) {
            foreach ($posData as $bachelorSpecialityId => $subPosData) {
                

                
                $speciality = $application->getSpecialities()
                    ->where([BachelorSpeciality::tableName() . '.id' => $bachelorSpecialityId])
                    ->one();
                if (empty($speciality)) {
                    continue;
                }
                if ($speciality->isWithoutEntranceTests) {
                    continue;
                }

                if ($speciality->is_enlisted) {
                    $bachelorEntranceTestSets = $speciality->bachelorEntranceTestSets;
                    if (!empty($bachelorEntranceTestSets)) {
                        foreach ($bachelorEntranceTestSets as $bachelorEntranceTestSet) {
                            $testSetNotToArchiveId[] = $bachelorEntranceTestSet->id;
                            $egeAttributesNotToArchive[] = $bachelorEntranceTestSet->entrance_test_junction;
                        }
                    }
                    continue;
                }

                if (!empty($subPosData)) {
                    foreach ($subPosData as $combinations) {
                        

                        $priority = ArrayHelper::getValue($combinations, 'priority');
                        $radioDisciplineId = ArrayHelper::getValue($combinations, 'cget_discipline_id');
                        $radioChildDisciplineId = ArrayHelper::getValue($combinations, 'cget_child_discipline_id');
                        foreach ($combinations as $combinationKey => $combinationData) {
                            if ($combinationKey == 'priority') {
                                continue;
                            }
                            

                            $disciplineId = (int)$radioDisciplineId;
                            if (empty($radioDisciplineId)) {
                                $checkboxDisciplineId = ArrayHelper::getValue($combinationData, 'cget_discipline_id');
                                if (empty($checkboxDisciplineId)) {
                                    continue;
                                }
                                $disciplineId = (int)$checkboxDisciplineId;
                            }
                            $childDisciplineId = (int)$radioChildDisciplineId;
                            if (empty($radioChildDisciplineId)) {
                                $checkboxChildDisciplineId = ArrayHelper::getValue($combinationData, 'cget_child_discipline_id');
                                if (!empty($checkboxChildDisciplineId)) {
                                    $childDisciplineId = (int)$checkboxChildDisciplineId;
                                }
                            }

                            $examFormId = (int)ArrayHelper::getValue($combinationData, 'cget_exam_form_id');
                            if (empty($examFormId)) {
                                continue;
                            }

                            [$set, $created] = EntrantTestManager::getOrCreateEntrantTestSet(
                                $application,
                                $speciality,
                                $disciplineId,
                                $childDisciplineId,
                                $examFormId,
                                $priority
                            );
                            $any_set_created = ($any_set_created || $created);
                            if (!in_array($set->entrance_test_junction, $egeAttributesNotToArchive)) {
                                $egeAttributesNotToArchive[] = $set->entrance_test_junction;
                            }
                            if (!in_array($set->id, $testSetNotToArchiveId)) {
                                $testSetNotToArchiveId[] = $set->id;
                            }
                        }
                    }
                }
            }
        }
        EntrantTestManager::archiveNotActualEntranceTestSetExceptReadOnly($application, $testSetNotToArchiveId);
        EntrantTestManager::archiveNotActualEgeExceptReadOnly($application, $egeAttributesNotToArchive);

        $afterLoad = EgeResult::getRelatedTestSets($application);

        return (
            $any_set_created ||
            count($beforeLoad) != count($afterLoad) ||
            count(array_diff($afterLoad, $beforeLoad)) > 0 ||
            count(array_diff($beforeLoad, $afterLoad)) > 0
        );
    }

    


    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        
        $centralizedTesting = $this->getRawBachelorResultCentralizedTesting()->one();
        if ($centralizedTesting && !$centralizedTesting->delete()) {
            return false;
        }

        
        $bachelorDatePassing = $this->getRawBachelorDatePassingEntranceTest()->one();
        if ($bachelorDatePassing && !$bachelorDatePassing->delete()) {
            return false;
        }

        return true;
    }

    




    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $cgetExamFormUid = '';
        $cgetDisciplineUid = '';
        $cgetChildDisciplineUid = '';
        $fieldList = [
            'cgetExamFormUid' => 'cgetExamForm',
            'cgetDisciplineUid' => 'cgetDiscipline',
            'cgetChildDisciplineUid' => 'cgetChildDiscipline',
        ];

        foreach ($fieldList as $varName => $field) {
            $valueReferenceUid = ArrayHelper::getValue($this, "{$field}.reference_uid", null);
            if ($valueReferenceUid) {
                $$varName = $valueReferenceUid;
            }
        }

        $this->entrance_test_junction = JointEntrantTestManager::buildJointEntrantTestString(
            $cgetDisciplineUid,
            $cgetExamFormUid,
            $cgetChildDisciplineUid
        );

        return true;
    }

    public function beforeArchive()
    {
        if (!CentralizedTestingManager::archiveIfExist($this->id)) {
            Yii::error('Ошибка архивирования ЦТ', 'beforeArchive');
            return false;
        }
        if (!ExamsScheduleManager::archiveIfExist($this->bachelorDatePassingEntranceTest)) {
            Yii::error('Ошибка архивирования расписания сдачи ВИ', 'beforeArchive');
            return false;
        }

        return true;
    }

    


    public function isExam()
    {
        $exam_form_uid = $this->cgetExamForm->reference_uid ?? null;
        return Yii::$app->configurationManager->getCode('discipline_ege_form') != $exam_form_uid;
    }

    


    public function getLanguageList(int $additional_language_id = null)
    {
        $tnForeignLanguage = ForeignLanguage::tableName();
        return ArrayHelper::map(
            ForeignLanguage::find()
                ->notMarkedToDelete()
                ->active()
                ->orFilterWhere(["{$tnForeignLanguage}.id" => $additional_language_id])
                ->orderBy(["{$tnForeignLanguage}.description" => SORT_ASC])
                ->all(),
            'id',
            'description'
        );
    }

    


    public function getSpecialRequirementList()
    {
        $tnSpecialRequirementReferenceType = SpecialRequirementReferenceType::tableName();
        return ArrayHelper::map(
            SpecialRequirementReferenceType::find()
                ->notMarkedToDelete()
                ->active()
                ->orFilterWhere([
                    "{$tnSpecialRequirementReferenceType}.id" => $this->special_requirement_ref_id
                ])
                ->orderBy(["{$tnSpecialRequirementReferenceType}.reference_name" => SORT_ASC])
                ->all(),
            'id',
            'reference_name'
        );
    }

    


    public function getReasonForExamList()
    {
        return ArrayHelper::map(
            DictionaryReasonForExam::find()
                ->active()
                ->orderBy(['name' => SORT_ASC])
                ->all(),
            'id',
            'name'
        );
    }

    


    public function getYears()
    {
        $years = [];
        $active_year = date('Y');
        for ($i = $active_year - 4; $i <= $active_year; $i++) {
            $years[(string)$i] = $i;
        }

        return $years;
    }

    


    public function hasChildren()
    {
        return isset($this->cget_child_discipline_id);
    }

    


    public function getMinimalMaximalScore()
    {
        if (empty($this->_minimalMaximalScore)) {
            $testsSetIds = ArrayHelper::map($this->application->specialities, 'id', 'cget_entrance_test_set_id');

            $test = CgetEntranceTest::find()
                ->joinWith('entranceTestResultSourceRef entranceTestResultSourceRef')
                ->select(['min_score'])
                ->where(['subject_ref_id' => $this->cget_discipline_id])
                ->andWhere(['in', 'cget_entrance_test_set_id', $testsSetIds])
                ->andWhere(['entranceTestResultSourceRef.reference_uid' => Yii::$app->configurationManager->getCode('discipline_ege_form')])
                ->groupBy(['min_score'])
                ->orderBy(['min_score' => SORT_DESC])
                ->one();

            if (empty($test)) {
                return '';
            }
            $this->minimalMaximalScore = $test->min_score;
        }
        return $this->_minimalMaximalScore;
    }

    


    public function setMinimalMaximalScore($minimalMaximalScore)
    {
        $this->_minimalMaximalScore = (string)$minimalMaximalScore;
    }

    




    public static function getAllDisciplineList()
    {
        $result = [];
        $disciplineEgeFormUid = Yii::$app->configurationManager->getCode('discipline_ege_form');

        $cgetEntranceTestTableName = CgetEntranceTest::tableName();
        $entranceTest = CgetEntranceTest::find()
            ->joinWith('entranceTestResultSourceRef entranceTestResultSourceRef')
            ->select("{$cgetEntranceTestTableName}.subject_ref_id")
            ->where([
                "{$cgetEntranceTestTableName}.archive" => false,
                "entranceTestResultSourceRef.reference_uid" => $disciplineEgeFormUid
            ])
            ->groupBy(["{$cgetEntranceTestTableName}.subject_ref_id"]);

        $storedDisciplineReferenceTypeTableName = StoredDisciplineReferenceType::tableName();
        $discipline = StoredDisciplineReferenceType::find()
            ->select(["{$storedDisciplineReferenceTypeTableName}.id", "{$storedDisciplineReferenceTypeTableName}.reference_name"])
            ->where(["{$storedDisciplineReferenceTypeTableName}.archive" => false])
            ->andWhere(['in', "{$storedDisciplineReferenceTypeTableName}.id", $entranceTest])
            ->all();

        if (!empty($discipline)) {
            $result = ArrayHelper::map($discipline, 'id', 'reference_name');
        }

        return $result;
    }

    public function afterValidate()
    {
        (new LoggingAfterValidateHandler())
            ->setModel($this)
            ->invoke();
    }

    


    public function getExamScheduleQuery()
    {
        
        $tnStoredDisciplineReferenceType = StoredDisciplineReferenceType::tableName();
        $tnStoredDisciplineFormReferenceType = StoredDisciplineFormReferenceType::tableName();

        
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnDictionaryPredmetOfExamsSchedule = DictionaryPredmetOfExamsSchedule::tableName();
        $tnDictionaryDateTimeOfExamsSchedule = DictionaryDateTimeOfExamsSchedule::tableName();
        return BachelorDatePassingEntranceTest::getExamScheduleBaseDetalizedQuery()
            ->andWhere([
                "{$tnDictionaryPredmetOfExamsSchedule}.archive" => false,
                "{$tnBachelorSpeciality}.archive" => false,
                "{$tnBachelorSpeciality}.application_id" => $this->application_id,
                "{$tnStoredDisciplineFormReferenceType}.reference_uid" => $this->cgetExamForm->reference_uid,
                "{$tnStoredDisciplineReferenceType}.reference_uid" => $this->cgetDiscipline->reference_uid,
            ])
            ->andWhere(['>', "{$tnDictionaryDateTimeOfExamsSchedule}.end_date", time()])
            ->andWhere([
                'OR',
                ['>', "{$tnDictionaryDateTimeOfExamsSchedule}.registration_date", time()],
                ['IN', "{$tnDictionaryDateTimeOfExamsSchedule}.registration_date", [0, null]],
            ]);
    }

    


    public function getExamSchedule()
    {
        return $this->getExamScheduleQuery()->exists();
    }

    




    public function getExamScheduleList($date)
    {
        $guidDateTimeList = [];
        $tnDictionaryDateTimeOfExamsSchedule = DictionaryDateTimeOfExamsSchedule::tableName();
        $dates = $this->getExamScheduleQuery()
            ->orderBy(["{$tnDictionaryDateTimeOfExamsSchedule}.start_date" => SORT_ASC])
            ->all();
        $dateId = $date->date_time_of_exams_schedule_id;

        $zeroKey = array_key_first($dates);
        $date->scheduleLabel = BachelorDatePassingEntranceTest::humanizer($dates[$zeroKey]['reference_name']);

        $examDateList = [];
        foreach ($dates as $data) {
            

            $guidDateTimeList[$data['id']] = $data['guid_date_time'];
            $examDateList[$data['id']] = BachelorDatePassingEntranceTest::generateName($data);
        }

        if (!in_array($dateId, $examDateList)) {
            $additionalDate = BachelorDatePassingEntranceTest::getExamScheduleBaseDetalizedQuery()
                ->andWhere(["{$tnDictionaryDateTimeOfExamsSchedule}.id" => $dateId])
                ->one();
            if (!empty($additionalDate)) {
                $guidDateTimeList[$additionalDate['id']] = $additionalDate['guid_date_time'];
                $examDateList[$additionalDate['id']] = BachelorDatePassingEntranceTest::generateName($additionalDate);
            }
        }

        if (!empty($guidDateTimeList)) {
            $date->existGuidDateTimeList = $guidDateTimeList;
            $date->hasChildren = DictionaryDateTimeOfExamsSchedule::find()
                ->where(['in', 'predmet_guid', $guidDateTimeList])
                ->exists();
        }

        return $examDateList;
    }

    public function getIdentityString(): string
    {
        $discipline_uid = ArrayHelper::getValue($this, 'cgetDiscipline.reference_uid', '');
        $child_discipline_uid = ArrayHelper::getValue($this, 'cgetChildDiscipline.reference_uid', '');
        $exam_form_uid = ArrayHelper::getValue($this, 'cgetExamForm.reference_uid', '');
        $egeyear = $this->egeyear;
        $discipline_points = $this->discipline_points;
        return "{$discipline_uid}_{$child_discipline_uid}_{$exam_form_uid}_{$egeyear}_{$discipline_points}";
    }

    public function getCgetDisciplineName()
    {
        return ArrayHelper::getValue($this, 'cgetDiscipline.reference_name');
    }

    public function getCgetChildDisciplineName()
    {
        return ArrayHelper::getValue($this, 'cgetChildDiscipline.reference_name');
    }

    public function getCgetExamFormName()
    {
        return ArrayHelper::getValue($this, 'cgetExamForm.reference_name');
    }

    public function getLanguageName()
    {
        return ArrayHelper::getValue($this, 'language.description');
    }

    public function getSpecialRequirementName()
    {
        return ArrayHelper::getValue($this, 'specialRequirement.reference_name');
    }

    public function getPropsToCompare(): array
    {
        return [
            'cgetDisciplineName',
            'cgetChildDisciplineName',
            'cgetExamFormName',
            'discipline_points',
            'egeyear',
            'languageName',
            'reasonForExamName',
            'specialRequirementName',
        ];
    }

    public function getRelationsInfo(): array
    {
        return [
            new OneToOneRelationPresenter(
                'bachelorResultCentralizedTesting',
                [
                    'parent_instance' => $this,
                    'child_class' => BachelorResultCentralizedTesting::class,
                    'child_column_name' => 'egeresult_id',
                ]
            ),
            new OneToOneRelationPresenter(
                'bachelorDatePassingEntranceTest',
                [
                    'parent_instance' => $this,
                    'child_class' => BachelorDatePassingEntranceTest::class,
                    'child_column_name' => 'bachelor_egeresult_id',
                    'actual_relation_name' => 'rawBachelorDatePassingEntranceTest',
                ]
            ),
        ];
    }

    public static function getArchiveValue()
    {
        return true;
    }

    public static function find()
    {
        return new ArchiveQuery(static::class);
    }

    




    public function isOnlyEgeForThisApplication(array $setsIdsToSkip = []): bool
    {
        $tnBachelorSpeciality = BachelorSpeciality::tableName();
        $tnBachelorEntranceTestSet = BachelorEntranceTestSet::tableName();

        return !BachelorEntranceTestSet::find()
            ->active()
            ->joinWith('bachelorSpeciality')
            ->andWhere(['NOT IN', "{$tnBachelorEntranceTestSet}.id", $setsIdsToSkip])
            ->andWhere(["{$tnBachelorSpeciality}.application_id" => $this->application_id])
            ->andWhere(["{$tnBachelorEntranceTestSet}.entrance_test_junction" => $this->entrance_test_junction])
            ->exists();
    }

    


    public function hasEnlistedBachelorSpecialities(): bool
    {
        $tn = BachelorSpeciality::tableName();
        return $this->getBachelorSpecialities()
            ->andWhere(["{$tn}.is_enlisted" => true])
            ->andWhere(["{$tn}.application_id" => $this->application_id])
            ->exists();
    }

    public function stringify(): string
    {
        return trim("{$this->disciplineReferenceName} " . ArrayHelper::getValue($this, 'cgetExamForm.reference_name'));
    }
}
