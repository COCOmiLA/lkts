<?php

namespace common\models\dictionary\StoredReferenceType\UpdateHandler;

use Closure;
use common\components\dictionaryManager\GetReferencesManager\GetReferencesManager;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\dictionary\StoredReferenceType\UpdateHandler\interfaces\IStoredReferenceTypeUpdateHandler;
use Yii;
use yii\base\UserException;






class BaseUpdateHandler implements IStoredReferenceTypeUpdateHandler
{
    


    private $storedReferenceType;

    public function __construct(StoredReferenceType $storedReferenceType)
    {
        $this->setStoredReferenceTypeToProceed($storedReferenceType);
    }

    


    public function update(Closure $onBeginUpdate = null, Closure $onNextReference = null, Closure $onEndUpdate = null)
    {
        $model = $this->getStoredReferenceTypeToProceed();

        $result = GetReferencesManager::getReferences($model::getReferenceClassToFill());

        $fields = [
            'reference_name' => 'ReferenceName',
            'reference_class_name' => 'ReferenceClassName',
            'reference_id' => 'ReferenceId',
            'reference_uid' => 'ReferenceUID',
        ];

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $orList = ['or'];
            $references = $result->getReferences();
            $model::updateAll(['archive' => true]);
            for ($I = 0; $I < count($references); $I++) {

                $reference = $references[$I];

                $andList = ['and'];
                foreach ($fields as $lkField => $field1C) {
                    if (property_exists($reference, $field1C)) {
                        $andList[] = [$lkField => $reference->{$field1C}];
                    }
                }
                if (!empty($andList)) {
                    $orList[] = $andList;
                }
            }
            $allData = $model::find()
                ->where($orList)
                ->all();

            $packageData = [];
            $countReferences = count($references);
            if (!is_null($onBeginUpdate)) {
                $onBeginUpdate($countReferences);
            }
            if (!empty($allData)) {
                $existData = [];
                for ($index = 0; $index < $countReferences; $index++) {
                    if (!is_null($onNextReference)) {
                        $onNextReference($index, $countReferences);
                    }
                    $reference = $references[$index];

                    $isExist = false;
                    for ($J = 0; $J < count($allData); $J++) {

                        $data = $allData[$J];

                        $objectData = (object)$data->buildReferenceTypeArrayTo1C();
                        $miniReference = (object)[
                            'ReferenceId' => $reference->ReferenceId,
                            'ReferenceUID' => $reference->ReferenceUID,
                            'ReferenceName' => $reference->ReferenceName,
                            'ReferenceClassName' => $reference->ReferenceClassName,
                        ];
                        if ($objectData == $miniReference) {
                            $existData[] = $data->id;
                            $isExist = true;

                            continue;
                        }
                    }
                    if ($isExist) {
                        continue;
                    }

                    $packageData[$index] = [];
                    foreach ($fields as $lkField => $field1C) {
                        if (property_exists($reference, $field1C)) {
                            $packageData[$index][$lkField] = $reference->{$field1C};
                        }
                    }
                    $packageData[$index]['archive'] = false;
                    $packageData[$index]['updated_at'] = time();
                    $packageData[$index]['created_at'] = time();
                }
                if (!empty($existData)) {
                    $updateResult = $model::updateAll(
                        ['archive' => false],
                        ['in', 'id', $existData]
                    );
                    if (!$updateResult) {
                        throw new UserException();
                    }
                }
            } else {
                for ($index = 0; $index < $countReferences; $index++) {

                    if (!is_null($onNextReference)) {
                        $onNextReference($index, $countReferences);
                    }
                    $reference = $references[$index];

                    $packageData[$index] = [];
                    foreach ($fields as $lkField => $field1C) {
                        if (property_exists($reference, $field1C)) {
                            $packageData[$index][$lkField] = $reference->{$field1C};
                        }
                    }
                    $packageData[$index]['archive'] = false;
                    $packageData[$index]['updated_at'] = time();
                    $packageData[$index]['created_at'] = time();
                }
            }
            $tableName = $model::tableName();
            $firstInPackage = array_key_first($packageData);
            $listToInsert = array_keys($packageData[$firstInPackage]);
            Yii::$app->db->createCommand()->batchInsert($tableName, $listToInsert, $packageData)->execute();

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            return false;
        }
        if (!is_null($onEndUpdate)) {
            $onEndUpdate();
        }
        return true;
    }

    


    public function getStoredReferenceTypeToProceed(): StoredReferenceType
    {
        return $this->storedReferenceType;
    }

    


    public function setStoredReferenceTypeToProceed(StoredReferenceType $referenceType)
    {
        $this->storedReferenceType = $referenceType;
    }
}