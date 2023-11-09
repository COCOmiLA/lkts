<?php

namespace common\components\EntrantTestManager;

use common\components\ReferenceTypeManager\ReferenceTypeManager;
use common\models\EmptyCheck;
use stdClass;
use Yii;
use yii\base\UserException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class BaseEntrantTestManager
{
    public const DATE_FORMAT_FOR_1C = 'Y-m-d\TH:i:s';
    public const EMPTY_DATE = '0001-01-01';

    
    public static $memorizeReferences = [];

    






    public static function extractUidFromRefType(
        string $class,
        string $name,
        array  $rawDataFrom1C
    ): string {
        $index = "{$class}_{$name}";
        $uid = ArrayHelper::getValue($rawDataFrom1C, "{$name}.ReferenceUID");
        $reference = ArrayHelper::getValue(BaseEntrantTestManager::$memorizeReferences, "{$index}.{$uid}");
        if (empty($reference)) {
            $reference = ReferenceTypeManager::GetOrCreateReference(
                $class,
                ArrayHelper::getValue(
                    $rawDataFrom1C,
                    $name
                )
            );
            if (!key_exists($index, BaseEntrantTestManager::$memorizeReferences)) {
                BaseEntrantTestManager::$memorizeReferences[$index] = [];
            }
            BaseEntrantTestManager::$memorizeReferences[$index][$uid] = $reference;
        }
        return ArrayHelper::getValue(
            $reference,
            'reference_uid',
            ''
        );
    }

    




    public static function archiveNotActualData(array $dataToArchive)
    {
        if (!empty($dataToArchive)) {
            $transaction = Yii::$app->db->beginTransaction();

            foreach ($dataToArchive as $toArchive) {
                

                if (!$toArchive->archive()) {
                    $transaction->rollBack();
                    $className = get_class($toArchive);
                    throw new UserException("Ошибка архивирования {$className}.");
                }
            }

            $transaction->commit();
        }
    }

    











    public static function errorMessageRecorder(
        string $message,
        array  $errors,
        string $action,
        array  &$msgBox = null
    ) {
        if (!is_null($msgBox)) {
            $msgBox[] = $message;
        } else {
            Yii::$app->session->setFlash(
                'alert',
                [
                    'body' => $message,
                    'options' => ['class' => 'alert-danger']
                ]
            );
        }
        Yii::error($message . PHP_EOL . print_r($errors, true), $action);
    }

    








    public static function successMessageRecorder(
        string $message,
        array  &$msgBox = null
    ) {
        if (!is_null($msgBox)) {
            $msgBox[] = $message;
        } else {
            Yii::$app->session->setFlash(
                'alert',
                [
                    'body' => $message,
                    'options' => ['class' => 'alert-success']
                ]
            );
        }
    }

    





    public static function postDataExtractor(
        array  $dataSource,
        string $path
    ) {
        return array_filter(
            ArrayHelper::getValue(
                $dataSource,
                $path,
                []
            ),
            function ($item) {
                return !EmptyCheck::isEmpty($item) && $item !== '0';
            }
        );
    }

    






    public static function makeArrayClone(array $dataSource)
    {
        return unserialize(serialize($dataSource));
    }
}
