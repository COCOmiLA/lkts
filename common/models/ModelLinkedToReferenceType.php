<?php


namespace common\models;


use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotFindReferenceException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\interfaces\ICanBeFoundByRefType;
use common\modules\student\models\ReferenceType;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;






class ModelLinkedToReferenceType extends ReferenceTypeModelFrom1C implements ICanBeFoundByRefType
{
    



    protected static $refKeyColumnName = 'ref_id';

    protected static $refClass = StoredReferenceType::class;

    
    
    public function buildReferenceTypeArrayTo1C(): array
    {
        $ref_model = null;
        if (!EmptyCheck::isEmpty(static::$refKeyColumnName) && !EmptyCheck::isEmpty(static::$refClass) && isset($this->{static::$refKeyColumnName})) {
            $ref_model = (static::$refClass)::find()->where(['id' => $this->{static::$refKeyColumnName}])->one();
        }
        return ReferenceTypeManager::GetReference($ref_model);
    }

    



    protected static $refAdditionalClasses = [];

    





    protected static $refColumns = [];

    






    public static function findByReferenceType($referenceData): ?ActiveRecord
    {
        if (EmptyCheck::isEmpty(static::$refKeyColumnName) || EmptyCheck::isEmpty(static::$refClass) || is_null($referenceData) || ReferenceTypeManager::isReferenceTypeEmpty($referenceData)) {
            return null;
        }

        $reference = ReferenceTypeManager::GetOrCreateReference(static::$refClass, $referenceData);
        if (is_null($reference)) {
            return null;
        }
        $query = static::find()->where([
            static::$refKeyColumnName => $reference->id,
        ]);
        if (static::isArchivable()) {
            $query->andWhere([
                static::$archiveColumnName => static::$archiveColumnNegativeValue
            ]);
        }
        return $query->one();
    }

    








    public function loadRefKeys($response1C, $create_if_not_found = true)
    {
        foreach (static::$refColumns as $column => $columnFrom1C) {
            if (isset($response1C->{$columnFrom1C}) && !EmptyCheck::isEmpty(ArrayHelper::getValue($response1C, $columnFrom1C)) && !ReferenceTypeManager::isReferenceTypeEmpty(ArrayHelper::getValue($response1C, $columnFrom1C))) {
                $refClass = (isset(static::$refAdditionalClasses[$column]) ? static::$refAdditionalClasses[$column] : static::$refClass);
                $reference = null;
                if ($create_if_not_found) {
                    $reference = ReferenceTypeManager::GetOrCreateReference($refClass, ArrayHelper::getValue($response1C, $columnFrom1C));
                } else {
                    $reference = ReferenceTypeManager::GetOrUpdateReference($refClass, ArrayHelper::getValue($response1C, $columnFrom1C));
                }
                if (!is_null($reference)) {
                    $this->{$column} = $reference->id;
                }
            }
        }
        return $this;
    }

    














    public function loadRefKeysWithCaching($response1C, bool $create_if_not_found, array &$refCacheList)
    {
        foreach (static::$refColumns as $column => $columnFrom1C) {
            if (isset($response1C->{$columnFrom1C})) {
                $stdClassFrom1C = ArrayHelper::getValue($response1C, $columnFrom1C);
                if (!ReferenceTypeManager::isReferenceTypeEmpty($stdClassFrom1C)) {
                    $referenceId = null;
                    $index = crc32(serialize($stdClassFrom1C));
                    $refClass = (static::$refAdditionalClasses[$column] ?? static::$refClass);

                    if (isset($refCacheList[$refClass]) && isset($refCacheList[$refClass][$index])) {
                        $referenceId = $refCacheList[$refClass][$index];
                    }
                    if (is_null($referenceId)) {
                        if ($create_if_not_found) {
                            $referenceId = ReferenceTypeManager::GetOrCreateReference($refClass, $stdClassFrom1C)->id;
                        } else {
                            $referenceId = ReferenceTypeManager::GetOrUpdateReference($refClass, $stdClassFrom1C)->id;
                        }
                        if (!isset($refCacheList[$refClass])) {
                            $refCacheList[$refClass] = [];
                        }
                        $refCacheList[$refClass][$index] = $referenceId;
                    }
                    $this->{$column} = $referenceId;
                }
            }
        }
        return $this;
    }

    





    public static function getReferenceTypeSearchArray($data, array &$cached_ids_list = null, array &$cached_all_matched_ids_list = null)
    {
        $arr = [];
        foreach (static::$refColumns as $column => $column1C) {
            if (isset($data->{$column1C}) && !EmptyCheck::isEmpty(ArrayHelper::getValue($data, $column1C)) && !ReferenceTypeManager::isReferenceTypeEmpty(ArrayHelper::getValue($data, $column1C))) {
                $refClass = (static::$refAdditionalClasses[$column] ?? static::$refClass);
                $referenceRawData = ArrayHelper::getValue($data, $column1C);
                $referenceMappedData = ReferenceType::BuildRefFromXML($referenceRawData);
                $crc_presentation = crc32(serialize($referenceRawData));
                $active_id = static::getActiveReferenceId($refClass, $referenceMappedData, $crc_presentation, $cached_ids_list);
                if ($active_id) {
                    
                    
                    $arr[$column] = static::getAllReferenceIds($refClass, $referenceMappedData, $cached_all_matched_ids_list);
                }
            }
        }
        return $arr;
    }

    protected static function getAllReferenceIds($refClass, $referenceMappedData, array &$cached_all_matched_ids_list = null): array
    {
        $is_enumerate = ReferenceTypeManager::isEnumerateReferenceType($referenceMappedData->referenceClassName);
        
        if (!$is_enumerate) {
            $key_presentation = $referenceMappedData->referenceUID;
            $reference_query = $refClass::getQuerySetByUID($referenceMappedData->referenceUID, true);
        } else {
            $key_presentation = $referenceMappedData->referenceName;
            $reference_query = $refClass::getQuerySetByName($referenceMappedData->referenceName, true);
        }
        if ($cached_all_matched_ids_list && isset($cached_all_matched_ids_list[$refClass][$key_presentation])) {
            return $cached_all_matched_ids_list[$refClass][$key_presentation];
        }

        $all_ids = $reference_query->select(['id'])->column();

        
        if (!is_null($cached_all_matched_ids_list)) {
            if (!isset($cached_all_matched_ids_list[$refClass])) {
                $cached_all_matched_ids_list[$refClass] = [];
            }
            $cached_all_matched_ids_list[$refClass][$key_presentation] = $all_ids;
        }

        return $all_ids;
    }

    protected static function getActiveReferenceId($refClass, $referenceMappedData, int $crc_presentation, array &$cached_ids_list = null): ?int
    {
        if ($cached_ids_list) {
            if (isset($cached_ids_list[$refClass][$crc_presentation])) {
                $cached_ref_id = $cached_ids_list[$refClass][$crc_presentation];
                if ($cached_ref_id) {
                    return $cached_ref_id;
                }
            }
        }
        $reference = ReferenceTypeManager::GetOrCreateReference($refClass, $referenceMappedData);
        if (!is_null($reference)) {
            if (!is_null($cached_ids_list)) {
                if (!isset($cached_ids_list[$refClass])) {
                    $cached_ids_list[$refClass] = [];
                }
                $cached_ids_list[$refClass][$crc_presentation] = $reference->id;
            }
            return $reference->id;
        }
        return null;
    }

    







    public function loadRefKey($referenceData)
    {
        $reference = ReferenceTypeManager::GetOrCreateReference(static::$refClass, $referenceData);
        $this->{static::$refKeyColumnName} = $reference->id;
    }
}