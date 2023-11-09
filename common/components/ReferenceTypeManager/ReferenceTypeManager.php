<?php

namespace common\components\ReferenceTypeManager;

use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotFindReferenceException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerCannotSerializeDataException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerValidationException;
use common\components\ReferenceTypeManager\exceptions\ReferenceManagerWrongReferenceTypeClassException;
use common\components\soapException;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\EmptyCheck;
use common\models\interfaces\ICanBuildReferenceTypeArrayTo1C;
use common\models\interfaces\IReferenceCanUpdate;
use common\models\interfaces\IRestorableReferenceDictionary;
use common\models\ModelFrom1CByOData;
use common\models\ModelLinkedToReferenceType;
use common\modules\student\models\ReferenceType;
use SoapVar;
use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class ReferenceTypeManager
{
    






    public static function SaveReferences(string $referenceClass, $referenceData = null, ReferenceType $referenceMappedData = null)
    {
        if (is_null($referenceMappedData)) {
            if (!is_null($referenceData)) {
                $referenceMappedData = ReferenceType::BuildRefFromXML($referenceData);
            } else {
                throw new ReferenceManagerCannotSerializeDataException();
            }
        }

        return self::UpdateReference(new $referenceClass(), $referenceMappedData);
    }

    





    public static function UpdateReference($reference, ReferenceType $referenceMappedData)
    {
        if (!($reference instanceof StoredReferenceType || $reference instanceof ModelFrom1CByOData || $reference instanceof IReferenceCanUpdate)) {
            throw new ReferenceManagerWrongReferenceTypeClassException(get_class($reference));
        }
        $reference->loadDataFromMappedReference($referenceMappedData);
        $reference->setUpdateScenario();
        if ($reference->validate()) {
            $reference->save(false);
        } else {
            throw new ReferenceManagerValidationException($reference->errors, $referenceMappedData);
        }

        return $reference;
    }

    






    public static function GetOrCreateReference(string $refClass, $referenceData)
    {
        
        if (static::isReferenceTypeEmpty($referenceData)) {
            return null;
        }
        $referenceMappedData = ReferenceType::BuildRefFromXML($referenceData);

        $class_instance = (new $refClass);
        
        if (!($class_instance instanceof StoredReferenceType || $class_instance instanceof ModelFrom1CByOData || $class_instance instanceof ModelLinkedToReferenceType)) {
            throw new ReferenceManagerWrongReferenceTypeClassException($refClass);
        }
        $reference = null;

        try {
            $reference = self::GetOrUpdateReference($refClass, $referenceMappedData);
        } catch (ReferenceManagerCannotFindReferenceException $e) {
            
        }

        if (is_null($reference) && !($class_instance instanceof ModelLinkedToReferenceType)) {
            $reference = self::SaveReferences($refClass, null, $referenceMappedData);
        }

        return $reference;
    }

    












    public static function GetOrCreateReferenceIdWithCaching(string $refClass, $referenceData, array &$cache_storage): int
    {
        $index = crc32(serialize($referenceData));
        if (isset($cache_storage[$refClass]) && isset($cache_storage[$refClass][$index])) {
            return $cache_storage[$refClass][$index];
        }
        $referenceMappedData = ReferenceType::BuildRefFromXML($referenceData);

        $referenceId = ReferenceTypeManager::GetOrCreateReference(
            $refClass,
            $referenceMappedData
        )->id;
        if (!isset($cache_storage[$refClass])) {
            $cache_storage[$refClass] = [];
        }
        $cache_storage[$refClass][$index] = $referenceId;
        return $referenceId;
    }

    






    public static function GetOrUpdateReference(string $refClass, $referenceData)
    {
        $class_instance = (new $refClass());
        
        if (!($class_instance instanceof StoredReferenceType || $class_instance instanceof ModelFrom1CByOData || $class_instance instanceof ModelLinkedToReferenceType)) {
            throw new ReferenceManagerWrongReferenceTypeClassException($refClass);
        }
        $referenceMappedData = ReferenceType::BuildRefFromXML($referenceData);

        $reference = self::SearchReference($refClass, $referenceMappedData);

        
        if (is_null($reference) && !($class_instance instanceof ModelLinkedToReferenceType)) {
            $is_enumerate = ReferenceTypeManager::isEnumerateReferenceType($referenceMappedData->referenceClassName);
            if (!$is_enumerate) {
                $reference_query = $refClass::getQuerySetByUID($referenceMappedData->referenceUID, true);
            } else {
                $reference_query = $refClass::getQuerySetByName($referenceMappedData->referenceName, true);
            }
            $reference_candidates = $reference_query->all();

            foreach ($reference_candidates as $reference_candidate) {
                if (is_null($reference)) {
                    $reference = $reference_candidate;
                }
                if ($reference_candidate->{$refClass::getDataVersionColumnName()} == $referenceMappedData->referenceDataVersion) {
                    $reference = $reference_candidate;
                    break; 
                }
                
                if ($reference && is_null($reference_candidate->{$refClass::getDataVersionColumnName()}) && !is_null($reference->{$refClass::getDataVersionColumnName()})) {
                    $reference = $reference_candidate;
                }
            }

            if ($reference) {
                
                if (
                    $is_enumerate ||
                    !$refClass::isArchivable() ||
                    EmptyCheck::isEmpty($reference->{$refClass::getDataVersionColumnName()}) ||
                    $reference->{$refClass::getDataVersionColumnName()} === $referenceMappedData->referenceDataVersion
                ) {
                    return ReferenceTypeManager::UpdateReference($reference, $referenceMappedData);
                } else {
                    $reference->archive();
                    $new_reference = ReferenceTypeManager::UpdateReference(new $refClass(), $referenceMappedData);
                    if (($new_reference instanceof IRestorableReferenceDictionary) && ReferenceTypeManager::containsSameData($reference, $new_reference)) {
                        $new_reference->restoreDictionary();
                    }
                    return $new_reference;
                }
            }
            throw new ReferenceManagerCannotFindReferenceException($referenceMappedData->toObject());
        }

        return $reference;
    }

    



    public static function containsSameData($old, $new): bool
    {
        if ($old->{$old::getNameColumnName()} != $new->{$new::getNameColumnName()}) {
            return false;
        }
        if ($old->{$old::getIdColumnName()} != $new->{$new::getIdColumnName()}) {
            return false;
        }
        if ($old->{$old::getUidColumnName()} != $new->{$new::getUidColumnName()}) {
            return false;
        }
        return true;
    }

    





    public static function SearchReference($refClass, ?ReferenceType $referenceMappedData)
    {
        if (is_null($referenceMappedData)) {
            return null;
        }
        
        return $refClass::findByReferenceType($referenceMappedData->toObject());
    }

    











    public static function GetReference(?ActiveRecord $model, $field = null, string $reference_class_name_for_empty_reference = '', $throwErrorOnEmptyRef = false)
    {
        $reference = null;
        if (!is_null($model)) {
            
            if (!is_null($field)) {
                $reference = static::getFieldFromPath($model, $field);
            } else {
                $reference = $model;
            }
        }

        if (is_null($reference) && $throwErrorOnEmptyRef) {
            $systemName = $model ? get_class($model) : 'Неизвестно';
            throw new UserException("В системе не найдена ссылка ReferenceType ({$systemName}). Необходимо обратиться к администратору для перезаполнения справочников ReferenceType в разделе \"Справочники\".");
        }

        return (is_null($reference) ? static::getEmptyRefTypeArray($reference_class_name_for_empty_reference) : $reference->buildReferenceTypeArrayTo1C());
    }

    public static function getEmptyRefTypeArray(string $class_name = '')
    {
        return [
            'ReferenceName' => '',
            'ReferenceId' => '',
            'ReferenceUID' => '00000000-0000-0000-0000-000000000000',
            'ReferenceClassName' => $class_name,
            'ReferenceParentUID' => '00000000-0000-0000-0000-000000000000',
            'ReferenceDataVersion' => '',
            'IsFolder' => 0,
            'DeletionMark' => 0,
            'Posted' => 0,
            'Predefined' => 0,
            'PredefinedDataName' => '',
        ];
    }

    







    public static function GetOneSFieldByRefTypeOrSimpleField($row_data, $ref_field_path, string $ref_column_name, string $simple_column_name): string
    {
        $ref_field_value = static::getFieldFromPath($row_data, $ref_field_path);
        if (!is_null($ref_field_value) && isset($ref_field_value->{$ref_column_name})) {
            return (string)($ref_field_value->{$ref_column_name});
        }
        return (string)($row_data->{$simple_column_name} ?? '');
    }

    




    protected static function getFieldFromPath($model, $field_path)
    {
        if (EmptyCheck::isEmpty($model)) {
            return null;
        }
        if (!is_array($field_path)) {
            $field_path = explode('.', $field_path);
        }
        $cur_value = $model;
        foreach ($field_path as $path_step) {
            if (isset($cur_value->{$path_step}) && !EmptyCheck::isEmpty($cur_value->{$path_step})) {
                $cur_value = $cur_value->{$path_step};
            } else {
                return null;
            }
        }
        return $cur_value;
    }

    



    public static function isReferenceTypeEmpty($data)
    {
        if (EmptyCheck::isEmpty($data)) {
            return true;
        }
        if ($data instanceof SoapVar) {
            $data = $data->enc_value;
        }
        $ReferenceClassName = null;
        $ReferenceUID = null;
        $ReferenceName = null;
        if (is_array($data) && ArrayHelper::isAssociative($data)) {
            $ReferenceClassName = $data['ReferenceClassName'];
            $ReferenceUID = $data['ReferenceUID'];
            $ReferenceName = $data['ReferenceName'];
        } elseif ($data instanceof ReferenceType) {
            $ReferenceClassName = $data->referenceClassName;
            $ReferenceUID = $data->referenceUID;
            $ReferenceName = $data->referenceName;
        } else {
            $ReferenceClassName = $data->ReferenceClassName ?? '';
            $ReferenceUID = $data->ReferenceUID ?? null;
            $ReferenceName = $data->ReferenceName ?? '';
        }
        if (self::isEnumerateReferenceType($ReferenceClassName)) {
            return EmptyCheck::isEmpty($ReferenceName ?? '');
        }
        return empty($ReferenceUID) || $ReferenceUID === '00000000-0000-0000-0000-000000000000';
    }

    




    public static function isEnumerateReferenceType($class_name)
    {
        return mb_strpos($class_name, 'Перечисление.') !== false;
    }

    








    public static function GetReferenceTypeFrom1C(?string $parameter, string $parameterRef, string $parameterType): ?ReferenceType
    {
        if (EmptyCheck::isEmpty($parameter)) {
            return null;
        }
        if (is_null($parameterType)) {
            if (preg_match('/Документ\..*/', $parameterRef)) {
                $parameterType = 'Номер';
            } else if (preg_match('/Справочник\..*/', $parameterRef)) {
                $parameterType = 'Код';
            } else {
                throw new UserException('Невозможно определить тип ссылки ReferenceType. Наименование ресурса не начинается с Справочник или Документ');
            }
        }
        \Yii::$app->session->removeFlash('UpdateReferenceDictionaryErrors');

        $data = [
            'Parameter' => $parameter,
            'ParameterType' => $parameterType,
            'ParameterRef' => $parameterRef,
        ];
        $response = \Yii::$app->soapClientAbit->load_with_caching('GetReference', $data);
        $errors = [];

        $referenceTypeResult = null;
        if ($response === false || !isset($response->return, $response->return->Reference)) {
            $errors[] = "Не найдена ссылка с кодом <strong>'{$parameter}'</strong> в справочнике <em>'{$parameterRef}'</em>";
            \Yii::warning(
                'Ошибка получения ReferenceType' . PHP_EOL . print_r(
                    [
                        'data' => $data,
                        'error' => [
                            'code' => ArrayHelper::getValue($response, 'return.Error.Code') ?? '-',
                            'description' => ArrayHelper::getValue($response, 'return.Error.Description') ?? '-',
                        ]
                    ],
                    true
                ),
                'GetReferenceTypeFrom1C'
            );

            $referenceTypeResult = ReferenceType::BuildRefFromXML(self::getEmptyRefTypeArray());
        } else {
            $referenceTypeResult = ReferenceType::BuildRefFromXML($response->return->Reference);
        }

        if (!empty($errors)) {
            \Yii::$app->session->setFlash('UpdateReferenceDictionaryErrors', $errors);
        }

        return $referenceTypeResult;
    }
}
