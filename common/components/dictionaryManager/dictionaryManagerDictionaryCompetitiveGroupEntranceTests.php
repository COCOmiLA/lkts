<?php

namespace common\components\dictionaryManager;

use common\components\helpers\TableCreateHelper;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\DictionaryCompetitiveGroupEntranceTest;
use common\models\dictionary\EducationType;
use common\models\dictionary\Privilege;
use common\models\dictionary\SpecialMark;
use common\models\dictionary\StoredReferenceType\StoredAdmissionCampaignReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCompetitiveGroupReferenceType;
use common\models\dictionary\StoredReferenceType\StoredConditionTypeReferenceType;
use common\models\dictionary\StoredReferenceType\StoredCurriculumReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineFormReferenceType;
use common\models\dictionary\StoredReferenceType\StoredDisciplineReferenceType;
use common\models\dictionary\StoredReferenceType\StoredProfileReferenceType;
use common\models\dictionary\StoredReferenceType\StoredSubjectSetReferenceType;
use common\models\managers\BatchMaker;
use common\modules\abiturient\models\bachelor\CgetChildSubject;
use common\modules\abiturient\models\bachelor\CgetConditionType;
use common\modules\abiturient\models\bachelor\CgetEntranceTest;
use common\modules\abiturient\models\bachelor\CgetEntranceTestSet;
use common\modules\abiturient\models\bachelor\CgetRequiredPreference;
use League\CLImate\TerminalObject\Dynamic\Progress;
use stdClass;
use Throwable;
use Yii;
use yii\db\Schema;
use yii\helpers\ArrayHelper;

class dictionaryManagerDictionaryCompetitiveGroupEntranceTests extends dictionaryManager
{
    private const TEMPORARY_TABLE_NAME = 'entrance_tests_tempo_table';

    private static $EMPTY_CGET_CONDITION_TYPE_LIST = [];

    


    public function init()
    {
        static::$EMPTY_CGET_CONDITION_TYPE_LIST = ['condition_type_reference_type_id' => null];
        foreach (array_keys(CgetConditionType::getJunctionListIdAndClass()) as $row) {
            static::$EMPTY_CGET_CONDITION_TYPE_LIST[$row] = null;
        }
    }

    


    private static function moveDataToDictionaryCompetitiveGroupEntranceTest(): void
    {
        $andRowsFilter = [
            'campaign_ref_id',
            'curriculum_ref_id',
            'competitive_group_ref_id',
            'allow_multiply_alternative_subjects',
        ];
        static::updateTemporaryTable(
            'dictionary_competitive_group_entrance_test',
            'dictionary_competitive_group_entrance_test_id',
            $andRowsFilter
        );
        static::unarchiveRefTable(
            'dictionary_competitive_group_entrance_test',
            'dictionary_competitive_group_entrance_test_id'
        );
        static::insertInRefTable(
            'dictionary_competitive_group_entrance_test',
            'dictionary_competitive_group_entrance_test_id',
            $andRowsFilter
        );
        static::updateTemporaryTable(
            'dictionary_competitive_group_entrance_test',
            'dictionary_competitive_group_entrance_test_id',
            $andRowsFilter,
            [],
            [],
            true
        );
    }

    


    private static function moveDataToCgetEntranceTestSet(): void
    {
        $andRowsFilter = [
            'entrance_test_set_ref_id',
            'dictionary_competitive_group_entrance_test_id',
        ];
        static::updateTemporaryTable(
            'cget_entrance_test_set',
            'cget_entrance_test_set_id',
            $andRowsFilter
        );
        static::unarchiveRefTable(
            'cget_entrance_test_set',
            'cget_entrance_test_set_id'
        );
        static::insertInRefTable(
            'cget_entrance_test_set',
            'cget_entrance_test_set_id',
            $andRowsFilter
        );
        static::updateTemporaryTable(
            'cget_entrance_test_set',
            'cget_entrance_test_set_id',
            $andRowsFilter,
            [],
            [],
            true
        );
    }

    


    private static function moveDataToCgetConditionType(): void
    {
        $cgetConditionTypeFieldList = array_keys(
            CgetConditionType::getJunctionListIdAndClass()
        );
        $andRowsFilter = array_merge(
            [
                'cget_entrance_test_set_id',
                
                
            ],
            $cgetConditionTypeFieldList
        );
        $isNullRowsFilter = [
            
            'cget_entrance_test_set_id',
        ];
        
        static::updateTemporaryTable(
            'cget_condition_type',
            'cget_condition_type_id',
            
            $andRowsFilter,
            [],
            
            $isNullRowsFilter,
        );
        static::unarchiveRefTable(
            'cget_condition_type',
            'cget_condition_type_id'
        );
        static::insertInRefTable(
            'cget_condition_type',
            'cget_condition_type_id',
            
            $andRowsFilter,
            $isNullRowsFilter
        );
        static::updateTemporaryTable(
            'cget_condition_type',
            'cget_condition_type_id',
            
            $andRowsFilter,
            [],
            
            $isNullRowsFilter,
            true
        );
        
    }

    


    private static function moveDataToCgetEntranceTest(): void
    {
        static::updateTemporaryTable(
            'cget_entrance_test',
            'cget_entrance_test_id',
            [
                'subject_ref_id',
                'entrance_test_result_source_ref_id',
                'cget_entrance_test_set_id',
            ],
            [
                'priority',
                'min_score',
            ]
        );
        static::unarchiveRefTable(
            'cget_entrance_test',
            'cget_entrance_test_id'
        );
        static::insertInRefTable(
            'cget_entrance_test',
            'cget_entrance_test_id',
            [
                'subject_ref_id',
                'entrance_test_result_source_ref_id',
                'cget_entrance_test_set_id',
                'priority',
                'min_score',
            ]
        );
        static::updateTemporaryTable(
            'cget_entrance_test',
            'cget_entrance_test_id',
            [
                'subject_ref_id',
                'entrance_test_result_source_ref_id',
                'cget_entrance_test_set_id',
            ],
            [
                'priority',
                'min_score',
            ],
            [],
            true
        );
    }

    


    private static function moveDataToCgetChildSubject(): void
    {
        $andRowsFilter = ['cget_entrance_test_id'];
        $orRowsFilter = [
            'child_subject_ref_id',
            'child_subject_index',
        ];
        $isNullRowsFilter = ['child_subject_ref_id'];
        static::updateTemporaryTable(
            'cget_child_subject',
            'child_entrance_test_id',
            $andRowsFilter,
            $orRowsFilter,
            $isNullRowsFilter,
        );
        static::unarchiveRefTable(
            'cget_child_subject',
            'child_entrance_test_id'
        );
        static::insertInRefTable(
            'cget_child_subject',
            'child_entrance_test_id',
            array_merge($andRowsFilter, $orRowsFilter),
            $isNullRowsFilter
        );
        static::updateTemporaryTable(
            'cget_child_subject',
            'child_entrance_test_id',
            $andRowsFilter,
            $orRowsFilter,
            $isNullRowsFilter,
            true
        );
    }

    


    private static function archiveEntrantTest(): void
    {
        CgetChildSubject::updateAll(['archive' => true]);
        CgetEntranceTest::updateAll(['archive' => true]);
        CgetConditionType::updateAll(['archive' => true]);
        CgetEntranceTestSet::updateAll(['archive' => true]);
        CgetRequiredPreference::updateAll(['archive' => true]);
        DictionaryCompetitiveGroupEntranceTest::updateAll(['archive' => true]);
    }

    





    private static function buildOrFilters(
        string $refTable,
        array  $rowsFilter
    ): array {
        $filters = [];
        $TEMPORARY_TABLE_NAME = static::TEMPORARY_TABLE_NAME;
        foreach ($rowsFilter as $row) {
            $filters[] = "(
                {$refTable}.{$row} = {$TEMPORARY_TABLE_NAME}.{$row} OR
                (
                    {$refTable}.{$row} IS NULL AND
                    {$TEMPORARY_TABLE_NAME}.{$row} IS NULL
                )
            )";
        }

        return $filters;
    }

    





    private static function buildAndFilters(
        string $refTable,
        array  $rowsFilter
    ): array {
        $filters = [];
        $TEMPORARY_TABLE_NAME = static::TEMPORARY_TABLE_NAME;
        foreach ($rowsFilter as $row) {
            $filters[] = "{$refTable}.{$row} = {$TEMPORARY_TABLE_NAME}.{$row}";
        }

        return $filters;
    }

    




    private static function buildIsNotNullFilters(array $isNullRowsFilter): array
    {
        $filters = [];
        $TEMPORARY_TABLE_NAME = static::TEMPORARY_TABLE_NAME;
        foreach ($isNullRowsFilter as $row) {
            $filters[] = "{$TEMPORARY_TABLE_NAME}.{$row} IS NOT NULL";
        }

        return $filters;
    }

    









    private static function updateTemporaryTable(
        string $refTable,
        string $refRow,
        array  $andRowsFilter = [],
        array  $orRowsFilter = [],
        array  $isNullRowsFilter = [],
        bool   $refRowIsNull = false
    ): void {
        $TEMPORARY_TABLE_NAME = static::TEMPORARY_TABLE_NAME;

        $filters = implode(
            ' AND ',
            array_merge(
                static::buildAndFilters($refTable, $andRowsFilter),
                static::buildOrFilters($refTable, $orRowsFilter),
            )
        );
        $query = "
            UPDATE {$TEMPORARY_TABLE_NAME}
            SET {$refRow} = (
                    SELECT id
                    FROM {$refTable}
                    WHERE {$filters}
                    LIMIT 1
                )
        ";
        if ($isNullRowsFilter || $refRowIsNull) {
            $query .= 'WHERE ' . implode(
                ' AND ',
                array_merge(
                    static::buildIsNotNullFilters($isNullRowsFilter),
                    $refRowIsNull ? ["{$refRow} IS NULL"] : []
                )
            );
        }

        Yii::$app->db->createCommand($query)->execute();
    }

    







    private static function insertInRefTable(
        string $refTable,
        string $refRow,
        array  $rowsUpdate = [],
        array  $isNullRowsFilter = []
    ): void {
        $TEMPORARY_TABLE_NAME = static::TEMPORARY_TABLE_NAME;
        $lists = [
            'insertRows' => ['archive', 'updated_at', 'created_at'],
            'selectRows' => ['FALSE',    time(),       time()],
        ];
        $insertRows = '';
        $selectRows = '';
        foreach ($lists as $varName => $item) {
            ${$varName} = implode(
                ', ',
                array_merge($rowsUpdate, $item)
            );
        }
        $query = "
            INSERT INTO {$refTable} ({$insertRows})
            SELECT DISTINCT {$selectRows}
            FROM {$TEMPORARY_TABLE_NAME}
            WHERE {$refRow} IS NULL
        ";
        if ($isNullRowsFilter) {
            $query .= ' AND ' . implode(
                ' AND ',
                static::buildIsNotNullFilters($isNullRowsFilter)
            );
        }

        Yii::$app->db->createCommand($query)->execute();
    }

    





    private static function unarchiveRefTable(
        string $refTable,
        string $refRow
    ): void {
        $TEMPORARY_TABLE_NAME = static::TEMPORARY_TABLE_NAME;

        $time = time();
        $query = "
            UPDATE {$refTable}
            SET archive = FALSE, updated_at = {$time}
            WHERE id IN (
                    SELECT {$refRow}
                    FROM {$TEMPORARY_TABLE_NAME}
                )
        ";

        Yii::$app->db->createCommand($query)->execute();
    }

    


    private static function buildConditionTypeForTemporaryTable(): array
    {
        $rowType = 'int NULL';
        $rows = ['cget_condition_type_id' => $rowType];
        foreach (array_keys(static::$EMPTY_CGET_CONDITION_TYPE_LIST) as $row) {
            $rows[$row] = $rowType;
        }

        return $rows;
    }

    


    private static function createTemporaryTable(): void
    {
        $tables_creator = Yii::createObject(TableCreateHelper::class);
        $tables_creator->createTempTable(
            static::TEMPORARY_TABLE_NAME,
            array_merge(
                [
                    'dictionary_competitive_group_entrance_test_id' => 'int',
                    'cget_entrance_test_set_id' => 'int',
                    'cget_entrance_test_id' => 'int',
                    'child_entrance_test_id' => 'int NULL',
                    'cget_required_preference_id' => 'int NULL',

                    'campaign_ref_id' => 'int NULL',
                    'curriculum_ref_id' => 'int NULL',
                    'competitive_group_ref_id' => 'int NULL',
                    'entrance_test_set_ref_id' => 'int NULL',
                    'subject_ref_id' => 'int NULL',
                    'entrance_test_result_source_ref_id' => 'int NULL',
                    'allow_multiply_alternative_subjects' => Yii::$app->db->getSchema()->createColumnSchemaBuilder(Schema::TYPE_BOOLEAN) . " NULL",
                    'priority' => 'int NULL',
                    'min_score' => 'int NULL',
                    'child_subject_index' => 'int NULL',
                    'child_subject_ref_id' => 'int NULL',
                ],
                static::buildConditionTypeForTemporaryTable()
            )
        );
    }

    




    private static function checkAndSetCacheStorage(array &$cache_storage): void
    {
        $needToCheck = [
            'classConverter' => CgetConditionType::getJunctionListRefClassNameAndClass(),
            'fieldConverter' => CgetConditionType::getJunctionListIdAndClass(),
        ];
        foreach ($needToCheck as $valueName => $defaultValue) {
            if (!key_exists($valueName, $cache_storage)) {
                $cache_storage[$valueName] = $defaultValue;
            }
        }
    }

    




    private function updateRequeuedReferences(?Progress $progress = null): void
    {
        
        $this->loadOneReferenceDictionary(StoredCurriculumReferenceType::class,        null, null, null, $progress);
        $this->loadOneReferenceDictionary(StoredCompetitiveGroupReferenceType::class,  null, null, null, $progress);
        $this->loadOneReferenceDictionary(StoredAdmissionCampaignReferenceType::class, null, null, null, $progress);

        
        $this->loadOneReferenceDictionary(Privilege::class,                  null, null, null, $progress);
        $this->loadOneReferenceDictionary(SpecialMark::class,                null, null, null, $progress);
        $this->loadOneReferenceDictionary(EducationType::class,              null, null, null, $progress);
        $this->loadOneReferenceDictionary(StoredProfileReferenceType::class, null, null, null, $progress);

        
        $this->loadOneReferenceDictionary(StoredDisciplineReferenceType::class,     null, null, null, $progress);
        $this->loadOneReferenceDictionary(StoredDisciplineFormReferenceType::class, null, null, null, $progress);

        
        $this->loadOneReferenceDictionary(StoredSubjectSetReferenceType::class, null, null, null, $progress);
    }

    


    private static function createIndexes(): void
    {
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-campaign_ref_id',                     static::TEMPORARY_TABLE_NAME, 'campaign_ref_id')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-curriculum_ref_id',                   static::TEMPORARY_TABLE_NAME, 'curriculum_ref_id')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-competitive_group_ref_id',            static::TEMPORARY_TABLE_NAME, 'competitive_group_ref_id')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-allow_multiply_alternative_subjects', static::TEMPORARY_TABLE_NAME, 'allow_multiply_alternative_subjects')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-entrance_test_set_ref_id',            static::TEMPORARY_TABLE_NAME, 'entrance_test_set_ref_id')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-cg_entrance_test_id',                 static::TEMPORARY_TABLE_NAME, 'dictionary_competitive_group_entrance_test_id')->execute();

        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-subject_ref_id',            static::TEMPORARY_TABLE_NAME, 'subject_ref_id')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-et_result_source_ref_id',   static::TEMPORARY_TABLE_NAME, 'entrance_test_result_source_ref_id')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-cget_entrance_test_set_id', static::TEMPORARY_TABLE_NAME, 'cget_entrance_test_set_id')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-child_subject_ref_id',      static::TEMPORARY_TABLE_NAME, 'child_subject_ref_id')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-child_subject_index',       static::TEMPORARY_TABLE_NAME, 'child_subject_index')->execute();
        Yii::$app->db->createCommand()->createIndex('idx-entrance_tempo_table-cget_entrance_test_id',     static::TEMPORARY_TABLE_NAME, 'cget_entrance_test_id')->execute();

        
        $rowToIndex = array_keys(static::buildConditionTypeForTemporaryTable());
        foreach ($rowToIndex as $row) {
            Yii::$app->db->createCommand()->createIndex("idx-entrance_tempo_table-{$row}", static::TEMPORARY_TABLE_NAME, $row)->execute();
        }
    }

    





    private static function GetOrCreateCgetConditionTypeIdsWithCaching(
        array  $AdmissionConditionGroups,
        array &$cache_storage
    ): array {
        static::checkAndSetCacheStorage($cache_storage);

        $conditionTypeList = [];
        $classConverter = $cache_storage['classConverter'];
        $fieldConverter = $cache_storage['fieldConverter'];
        foreach ($AdmissionConditionGroups as $I => $conditionGroups) {
            

            $conditionTypeList[$I] = static::$EMPTY_CGET_CONDITION_TYPE_LIST;
            if (!isset($conditionGroups->AdmissionConditions)) {
                continue;
            }
            if (!is_array($conditionGroups->AdmissionConditions)) {
                $conditionGroups->AdmissionConditions = [$conditionGroups->AdmissionConditions];
            }
            foreach ($conditionGroups->AdmissionConditions as $condition) {
                
                if (ReferenceTypeManager::isReferenceTypeEmpty($condition->ConditionTypeRef)) {
                    continue;
                }

                $referenceConditionTypeRefId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                    StoredConditionTypeReferenceType::class,
                    $condition->ConditionTypeRef,
                    $cache_storage
                );

                if (ReferenceTypeManager::isReferenceTypeEmpty($condition->ConditionRef)) {
                    continue;
                }
                if (!key_exists($condition->ConditionRef->ReferenceClassName, $classConverter)) {
                    continue;
                }

                $referenceConditionRefId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                    $classConverter[$condition->ConditionRef->ReferenceClassName],
                    $condition->ConditionRef,
                    $cache_storage
                );

                $conditionRefField = array_search($classConverter[$condition->ConditionRef->ReferenceClassName], $fieldConverter);
                
                
                $conditionTypeList[$I][$conditionRefField] = $referenceConditionRefId;
            }
        }

        return $conditionTypeList;
    }

    public function loadDictionary(?Progress $progress = null)
    {
        gc_disable();
        $competitiveGroupEntranceTests = $this->getGetEntrantTestSetsGenerator();
        $disciplines_result = 1;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->updateRequeuedReferences($progress);

            $progressCount = 0;
            $percentPiece = 0;
            if ($progress) {
                $competitiveGroupEntranceTests = iterator_to_array($competitiveGroupEntranceTests);
                $progressCount = count($competitiveGroupEntranceTests);
                $percentPiece = $progressCount * 0.1;
                $totalCount = $progressCount * (1 + 5 * $percentPiece) + 1;
                $progress->total($progressCount * (1 + 5 * $percentPiece) + 1);
            }
            static::archiveEntrantTest();
            static::createTemporaryTable();

            $batcher = new BatchMaker(20000, function ($batch) {
                if (empty($batch)) {
                    return;
                }
                Yii::$app->db->createCommand()
                    ->batchInsert(static::TEMPORARY_TABLE_NAME, array_keys($batch[0]), $batch)
                    ->execute();
            });
            $errors_msg = [];

            $cache_storage = [];
            foreach ($competitiveGroupEntranceTests as $I => $competitiveGroupEntranceTestSet) {
                if ($progress && $I % 3 == 0) {
                    $progress->current($I);
                }

                $referenceCampaignRefId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                    StoredAdmissionCampaignReferenceType::class,
                    $competitiveGroupEntranceTestSet->CampaignRef,
                    $cache_storage
                );
                $referenceCurriculumRefId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                    StoredCurriculumReferenceType::class,
                    $competitiveGroupEntranceTestSet->CurriculumRef,
                    $cache_storage
                );
                $referenceCompetitiveGroupRefId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                    StoredCompetitiveGroupReferenceType::class,
                    $competitiveGroupEntranceTestSet->CompetitiveGroupRef,
                    $cache_storage
                );

                $allowMultiplyAlternativeSubjects = ArrayHelper::getValue($competitiveGroupEntranceTestSet, "AllowMultiplyAlternativeSubjects", false);
                $competitiveGroupEntranceTestSetData = [
                    'campaign_ref_id' => $referenceCampaignRefId,
                    'curriculum_ref_id' => $referenceCurriculumRefId,
                    'competitive_group_ref_id' => $referenceCompetitiveGroupRefId,
                    'allow_multiply_alternative_subjects' => $allowMultiplyAlternativeSubjects,
                ];


                if (isset($competitiveGroupEntranceTestSet->EntranceTestSets)) {
                    if (!is_array($competitiveGroupEntranceTestSet->EntranceTestSets)) {
                        $competitiveGroupEntranceTestSet->EntranceTestSets = [$competitiveGroupEntranceTestSet->EntranceTestSets];
                    }
                    $EntranceTestSetsCount = count($competitiveGroupEntranceTestSet->EntranceTestSets);
                    for ($j = 0; $j < $EntranceTestSetsCount; $j++) {
                        $entranceTestSets = $competitiveGroupEntranceTestSet->EntranceTestSets[$j];

                        $cgetConditionTypeList = [static::$EMPTY_CGET_CONDITION_TYPE_LIST];
                        if (isset($entranceTestSets->AdmissionConditionGroups)) {
                            if (!is_array($entranceTestSets->AdmissionConditionGroups)) {
                                $entranceTestSets->AdmissionConditionGroups = [$entranceTestSets->AdmissionConditionGroups];
                            }
                            $cgetConditionTypeList = static::GetOrCreateCgetConditionTypeIdsWithCaching(
                                $entranceTestSets->AdmissionConditionGroups,
                                $cache_storage
                            );
                        }

                        $referenceEntranceTestSetId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                            StoredSubjectSetReferenceType::class,
                            $entranceTestSets->EntranceTestSetRef,
                            $cache_storage
                        );

                        $EntranceTestSetData = ['entrance_test_set_ref_id' => $referenceEntranceTestSetId];
                        if (isset($entranceTestSets->EntranceTests)) {
                            if (!is_array($entranceTestSets->EntranceTests)) {
                                $entranceTestSets->EntranceTests = [$entranceTestSets->EntranceTests];
                            }
                            $cgetEntranceTestsCount = count($entranceTestSets->EntranceTests);
                            for ($k = 0; $k < $cgetEntranceTestsCount; $k++) {
                                $entranceTests = $entranceTestSets->EntranceTests[$k];

                                $referenceSubjectId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                                    StoredDisciplineReferenceType::class,
                                    $entranceTests->SubjectRef,
                                    $cache_storage
                                );

                                $referenceEntranceTestResultSourceId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                                    StoredDisciplineFormReferenceType::class,
                                    $entranceTests->EntranceTestResultSourceRef,
                                    $cache_storage
                                );
                                $EntranceTestData = [
                                    'subject_ref_id' => $referenceSubjectId,
                                    'entrance_test_result_source_ref_id' => $referenceEntranceTestResultSourceId,
                                    'priority' => (int)$entranceTests->Priority,
                                    'min_score' => (int)$entranceTests->MinScore,
                                ];
                                $added_with_child = false;
                                if (isset($entranceTests->ChildSubjects)) {
                                    if (!is_array($entranceTests->ChildSubjects)) {
                                        $entranceTests->ChildSubjects = [$entranceTests->ChildSubjects];
                                    }
                                    $ChildSubjectsCount = count($entranceTests->ChildSubjects);
                                    for ($l = 0; $l < $ChildSubjectsCount; $l++) {
                                        $childSubjects = $entranceTests->ChildSubjects[$l];

                                        $referenceChildSubjectRefId = ReferenceTypeManager::GetOrCreateReferenceIdWithCaching(
                                            StoredDisciplineReferenceType::class,
                                            $childSubjects->ChildSubjectRef,
                                            $cache_storage
                                        );
                                        foreach ($cgetConditionTypeList as $cgetConditionTypeItem) {
                                            $batcher->add(array_merge(
                                                $competitiveGroupEntranceTestSetData,
                                                $EntranceTestSetData,
                                                $EntranceTestData,
                                                [
                                                    'child_subject_ref_id' => $referenceChildSubjectRefId,
                                                    'child_subject_index' => (int)$childSubjects->ChildSubjectIndex,
                                                ],
                                                $cgetConditionTypeItem
                                            ));
                                        }

                                        $added_with_child = true;
                                    }
                                    unset($entranceTests->ChildSubjects);
                                }
                                if (!$added_with_child) {
                                    foreach ($cgetConditionTypeList as $cgetConditionTypeItem) {
                                        $batcher->add(array_merge(
                                            $competitiveGroupEntranceTestSetData,
                                            $EntranceTestSetData,
                                            $EntranceTestData,
                                            [
                                                'child_subject_ref_id' => null,
                                                'child_subject_index' => null,
                                            ],
                                            $cgetConditionTypeItem
                                        ));
                                    }
                                }
                            }
                            unset($entranceTestSets->EntranceTests);
                        }
                    }
                    unset($competitiveGroupEntranceTestSet->EntranceTestSets);
                }
                unset($competitiveGroupEntranceTestSet);
            }
            $batcher->flush();

            
            static::createIndexes();

            $J = 1;
            
            static::moveDataToDictionaryCompetitiveGroupEntranceTest();
            if ($progress && $progressCount) {
                $progress->current($progressCount * (1 + $J * $percentPiece));
            }
            $J++;

            static::moveDataToCgetEntranceTestSet();
            if ($progress && $progressCount) {
                $progress->current($progressCount * (1 + $J * $percentPiece));
            }
            $J++;

            static::moveDataToCgetConditionType();
            if ($progress && $progressCount) {
                $progress->current($progressCount * (1 + $J * $percentPiece));
            }
            $J++;

            static::moveDataToCgetEntranceTest();
            if ($progress && $progressCount) {
                $progress->current($progressCount * (1 + $J * $percentPiece));
            }
            $J++;

            static::moveDataToCgetChildSubject();
            if ($progress && $progressCount) {
                $progress->current($progressCount * (1 + $J * $percentPiece));
            }

            $transaction->commit();

            if ($progress && $progressCount) {
                $progress->current($totalCount);
            }
        } catch (Throwable $e) {
            $transaction->rollBack();
            $disciplines_result = -1;
            $errors_msg = $e;
        }

        return [
            $disciplines_result,
            $errors_msg
        ];
    }
}
