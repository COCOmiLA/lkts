<?php

namespace common\models\dictionary;

use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\traits\ScenarioWithoutExistValidationTrait;
use common\modules\abiturient\models\bachelor\BachelorApplication;
use common\modules\abiturient\models\bachelor\BachelorSpeciality;
use common\modules\abiturient\models\bachelor\CgetChildSubject;
use common\modules\abiturient\models\bachelor\CgetConditionType;
use common\modules\abiturient\models\bachelor\CgetEntranceTest;
use common\modules\abiturient\models\bachelor\CgetEntranceTestSet;
use common\services\abiturientController\bachelor\bachelorSpeciality\BachelorSpecialityService;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ArrayDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

















class DictionaryCompetitiveGroupEntranceTest extends ActiveRecord
{
    use ScenarioWithoutExistValidationTrait;

    
    public $educationTypeRefList = [];

    


    public static function tableName()
    {
        return '{{%dictionary_competitive_group_entrance_test}}';
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
                    'campaign_ref_id',
                    'curriculum_ref_id',
                    'competitive_group_ref_id',
                    'updated_at',
                    'created_at',
                ],
                'integer'
            ],
            [
                ['allow_multiply_alternative_subjects', 'archive'],
                'boolean'
            ],
            [
                ['campaign_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredAdmissionCampaignReferenceType::class,
                'targetAttribute' => ['campaign_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['competitive_group_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredCompetitiveGroupReferenceType::class,
                'targetAttribute' => ['competitive_group_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
            [
                ['curriculum_ref_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => StoredCurriculumReferenceType::class,
                'targetAttribute' => ['curriculum_ref_id' => 'id'],
                'except' => [static::$SCENARIO_WITHOUT_EXISTS_CHECK]
            ],
        ];
    }

    


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'campaign_ref_id' => 'Campaign Ref ID',
            'curriculum_ref_id' => 'Curriculum Ref ID',
            'competitive_group_ref_id' => 'Competitive Group Ref ID',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    




    public function getCampaignRef()
    {
        return $this->hasOne(StoredAdmissionCampaignReferenceType::class, ['id' => 'campaign_ref_id']);
    }

    




    public function getCgetEntranceTestSets()
    {
        return $this->hasMany(CgetEntranceTestSet::class, ['dictionary_competitive_group_entrance_test_id' => 'id']);
    }

    




    public function getCompetitiveGroupRef()
    {
        return $this->hasOne(StoredCompetitiveGroupReferenceType::class, ['id' => 'competitive_group_ref_id']);
    }

    




    public function getCurriculumRef()
    {
        return $this->hasOne(StoredCurriculumReferenceType::class, ['id' => 'curriculum_ref_id']);
    }

    









    private static function findBySpeciality(
        Speciality                            $speciality,
        ?StoredAdmissionCampaignReferenceType $campaignRef = null,
        array                                 $educationTypeRefUids = [],
        array                                 $profileRefUids = [],
        array                                 $privilegeUids = [],
        array                                 $specialMarkListUids = []
    ): array {
        $curriculumRef = $speciality->curriculumRef;
        $competitiveGroupRef = $speciality->competitiveGroupRef;
        if (empty($curriculumRef) || empty($competitiveGroupRef)) {
            return [];
        }

        $tnCompetitiveGroupEntranceTest = DictionaryCompetitiveGroupEntranceTest::tableName();
        $sets = CgetEntranceTestSet::find()
            ->joinWith('dictionaryCompetitiveGroupEntranceTest')
            ->andWhere(["{$tnCompetitiveGroupEntranceTest}.archive" => false]);

        $filterDatas = [
            [
                
                'tableName' => StoredAdmissionCampaignReferenceType::tableName(),
                'joinWith' => 'campaignRef',
                'refData' => $campaignRef,
            ],
            [
                
                'tableName' => StoredCurriculumReferenceType::tableName(),
                'joinWith' => 'curriculumRef',
                'refData' => $curriculumRef,
            ],
            [
                
                'tableName' => StoredCompetitiveGroupReferenceType::tableName(),
                'joinWith' => 'competitiveGroupRef',
                'refData' => $competitiveGroupRef,
            ],
        ];
        foreach ($filterDatas as $filterData) {
            [
                'tableName' => $tableName,
                'joinWith' => $joinWith,
                'refData' => $refData,
            ] = $filterData;
            $sets = DictionaryCompetitiveGroupEntranceTest::additionalFilter(
                $sets,
                $refData,
                $joinWith,
                $tableName
            );
        }

        $filterDatas = [
            'privilege_id' => $privilegeUids,
            'special_mark_id' => $specialMarkListUids,
            'profile_reference_type_id' => $profileRefUids,
            'dictionary_education_type_id' => $educationTypeRefUids,
        ];
        foreach ($filterDatas as $filterRow => $filterData) {
            $sets = DictionaryCompetitiveGroupEntranceTest::additionalFilterByConditions(
                $sets,
                $filterRow,
                $filterData
            );
        }

        return $sets->all();
    }

    







    private static function additionalFilter(
        ActiveQuery   $sets,
        ?ActiveRecord $refData,
        string        $joinWith,
        string        $tableName
    ): ActiveQuery {
        return $sets->joinWith("dictionaryCompetitiveGroupEntranceTest.{$joinWith}")
            ->andWhere([
                "{$tableName}.archive" => false,
                "{$tableName}.reference_uid" => $refData->reference_uid
            ]);
    }

    






    private static function additionalFilterByConditions(
        ActiveQuery $sets,
        string      $filterRow,
        array       $filterData
    ): ActiveQuery {
        $tnEntranceTestSet = CgetEntranceTestSet::tableName();
        if (!($buildAttributesForFiltering = CgetConditionType::buildAttributesForFiltering($filterRow))) {
            return $sets;
        }

        [
            'filterJoin' => $filterJoin,
            'filterTableName' => $filterTableName,
            'referenceUidField' => $referenceUidField,
        ] = $buildAttributesForFiltering;

        $sets = $sets->joinWith("cgetConditionTypes.$filterJoin")
            ->andOnCondition(["{$tnEntranceTestSet}.archive" => false]);
        if (!$filterData) {
            return $sets->andWhere(["{$filterTableName}.id" => null]);
        }
        return $sets->andWhere([
            'OR',
            ["{$filterTableName}.id" => null],
            [
                'AND',
                ["{$filterTableName}.archive" => false],
                ['IN', "{$filterTableName}.{$referenceUidField}", $filterData],
            ]
        ]);
    }

    public static function getDataProviderByApplication(BachelorApplication $application): ArrayDataProvider
    {
        $cache = [];
        $data = [];
        $sortedData = [];
        $campaignRef = $application->type->rawCampaign->referenceType;

        $tnSpecialMark = SpecialMark::tableName();
        $specialMarkListUids = $application->getPreferences()
            ->select("{$tnSpecialMark}.ref_key")
            ->joinWith('specialMark')
            ->andWhere(["{$tnSpecialMark}.archive" => false])
            ->column();

        $specialitiesList = $application->specialities;

        $bachelorSpecialityService = Yii::createObject(BachelorSpecialityService::class);
        $hierarchicalSpecialities = $bachelorSpecialityService->makeSpecialitiesListHierarchical($specialitiesList);
        
        $specialitiesList = $bachelorSpecialityService->flattenSpecialities($hierarchicalSpecialities);

        foreach ($specialitiesList as $specialities) {
            

            $educationTypeRefUids = $specialities->getEducationsRefAttributeUidByPath('educationType');
            $profileRefUids = $specialities->getEducationsRefAttributeUidByPath('profileRef');
            $privilegeUids = [];
            $privilegeUid = ArrayHelper::getValue($specialities, 'preference.privilege.ref_key');
            if ($privilegeUid) {
                $privilegeUids = [$privilegeUid];
            }

            $key = $specialities->id;

            $data["{$key}_"] = [];
            $speciality = $specialities->speciality;
            $priorityList = [];
            $rowspan = 1;
            $data["{$key}_"]['priority'] = '';
            $data["{$key}_"]['minScore'] = '';
            $data["{$key}_"]['exam_form'] = '';
            $data["{$key}_"]['allowMulti'] = '';
            $data["{$key}_"]['discipline'] = '';
            $data["{$key}_"]['parentDiscipline'] = '';
            $direction_name = $speciality->directionRef->reference_name ?? '';
            $group_name = $speciality->competitiveGroupRef->reference_name ?? '';
            $data["{$key}_"]['subject'] = ['value' => "{$direction_name} {$group_name}"];
            $data["{$key}_"]['bachelorSpecialityModel'] = $specialities;
            if ($specialities->isWithoutEntranceTests) {
                $data["{$key}_"]['priority'] = [
                    'value' => Yii::$app->configurationManager->getText('text_for_set_when_speciality-bvi'),
                    'rowspan' => 0,
                    'colspan' => 4,
                ];
                $data["{$key}_"]['subject']['rowspan'] = $rowspan;
                $sortedData["{$key}_"] = $data["{$key}_"];

                continue;
            }

            $needEmptyMessage = false;

            
            $sets = DictionaryCompetitiveGroupEntranceTest::findBySpeciality(
                $speciality,
                $campaignRef,
                $educationTypeRefUids,
                $profileRefUids,
                $privilegeUids,
                $specialMarkListUids
            );
            if (!empty($sets)) {
                foreach ($sets as $set) {
                    

                    
                    $tests = $set->cgetEntranceTests;
                    foreach ($tests as $test) {
                        

                        $parentSubjectRef = 0;
                        $subjectList = $test->cgetChildSubjects;
                        if (!$subjectList) {
                            $subjectList = [$test];
                        } else {
                            $parentSubjectRef = self::loadFromCache(
                                $test,
                                'subjectRef.id',
                                $test->subject_ref_id,
                                $cache
                            );
                        }
                        foreach ($subjectList as $subject) {
                            $rowspan = DictionaryCompetitiveGroupEntranceTest::subjectAssembly(
                                $data,
                                $subject,
                                $test,
                                $set,
                                $specialities,
                                $priorityList,
                                $key,
                                $rowspan,
                                $parentSubjectRef,
                                $cache
                            );
                        }
                    }
                }
            } else {
                $needEmptyMessage = true;
            }

            if ($needEmptyMessage) {
                $message = Yii::$app->configurationManager->getText('text_for_an_empty_line_when_it_was_not_possible_to_collect_a_set_of_entrance_tests');

                $data["{$key}_"]['priority'] = [
                    'value' => $message,
                    'rowspan' => 0,
                    'colspan' => 4,
                ];
                $data["{$key}_"]['subject']['rowspan'] = $rowspan;
                $sortedData["{$key}_"] = $data["{$key}_"];
            }

            $data["{$key}_"]['subject']['rowspan'] = $rowspan;
            $sortedData["{$key}_"] = $data["{$key}_"];
            ksort($priorityList);
            foreach ($priorityList as $priority => $value) {
                foreach (array_keys($value['discipline']) as $indexPart) {
                    $index = "{$indexPart}_{$priority}";
                    $sortedData[$index] = $data[$index];
                }
            }
        }

        return new ArrayDataProvider([
            'allModels' => $sortedData,
            'sort' => false,
            'pagination' => ['pageSize' => 1000],
        ]);
    }

    







    private static function loadFromCache(
        ActiveRecord $abstraction,
        string       $path,
        int          $refValue,
        array       &$cache
    ) {
        $key = "{$path}_{$refValue}";
        if (key_exists($key, $cache)) {
            return $cache[$key];
        }
        $cache[$key] = ArrayHelper::getValue($abstraction, $path);

        return $cache[$key];
    }

    















    private static function subjectAssembly(
        array &$data,
        object $subject,
        object $test,
        object $set,
        object $specialities,
        array &$priorityList,
        string $key,
        int    $rowspan,
        int    $parentSubjectRef,
        array &$cache
    ): int {
        $subjectRefId = $subject instanceof CgetEntranceTest ? 'subject_ref_id' : 'child_subject_ref_id';

        $subjectRef = self::loadFromCache(
            $subject,
            'subjectRef',
            $subject->{$subjectRefId},
            $cache
        );
        $entranceTestResultSourceRef = self::loadFromCache(
            $test,
            'entranceTestResultSourceRef',
            $test->entrance_test_result_source_ref_id,
            $cache
        );

        $I = "{$key}_{$subjectRef->id}_{$test->priority}";

        if (!array_key_exists($I, $data)) {
            $rowspan++;
            $data[$I]['subject'] = '';
        }

        if (!isset($data[$I]['priority'])) {
            $data[$I]['priority'] = [
                'value' => $test->priority,
                'rowspan' => 0,
            ];
        }

        $data[$I]['allowMulti'] = self::loadFromCache(
            $set,
            'allowMultiplyAlternativeSubjects',
            $set->dictionary_competitive_group_entrance_test_id,
            $cache
        );
        if ($parentSubjectRef) {
            $data[$I]['parentDiscipline'] = empty($parentSubjectRef) ? '' : $parentSubjectRef;
        }

        if (!in_array($test->priority, array_keys($priorityList))) {
            $priorityList[$test->priority]['count'] = 1;
            $priorityList[$test->priority]['discipline'] = ["{$key}_{$subjectRef->id}" => $subjectRef->id];
            $data[$I]['priority'] = [
                'value' => $test->priority,
                'rowspan' => &$priorityList[$test->priority]['count'],
            ];
        }
        if (!in_array($subjectRef->id, $priorityList[$test->priority]['discipline'])) {
            $priorityList[$test->priority]['discipline']["{$key}_{$subjectRef->id}"] = $subjectRef->id;
        }
        $priorityList[$test->priority]['count'] = count($priorityList[$test->priority]['discipline']);

        $data[$I]['min_score'] = $test->min_score;
        $data[$I]['discipline'] = $subjectRef->reference_name;
        $data[$I]['bachelorSpecialityModel'] = $specialities;
        if (isset($data[$I]['exam_form'])) {
            $data[$I]['exam_form'][$entranceTestResultSourceRef->id] = [
                'minScore' => $test->min_score,
                'name' => $entranceTestResultSourceRef->reference_name,
            ];
        } else {
            $data[$I]['exam_form'] = [$entranceTestResultSourceRef->id => [
                'minScore' => $test->min_score,
                'name' => $entranceTestResultSourceRef->reference_name,
            ]];
        }

        return $rowspan;
    }
}
