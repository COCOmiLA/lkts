<?php


namespace common\models\interfaces;

use yii\db\ActiveRecord;

interface ICanBeFoundByRefType
{
    public static function findByReferenceType($referenceData): ?ActiveRecord;
}