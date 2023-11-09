<?php


namespace common\models;


use yii\db\ActiveRecord;

class CodeModelFrom1C extends ActiveRecord
{
    protected static $codeColumnName = 'code';

    protected static $archiveColumnName = 'archive';

    protected static $archiveColumnPositiveValue = true;

    protected static $archiveColumnNegativeValue = false;

    public static function isArchivable(): bool
    {
        return (bool)static::getTableSchema()->getColumn(static::$archiveColumnName);
    }

    public static function findByCode($code)
    {
        $code = (string)$code;
        if (EmptyCheck::isEmpty($code)) {
            return null;
        }
        $query = static::find()->where([
            static::$codeColumnName => $code,
        ]);

        if (static::isArchivable()) {
            $query->andWhere([
                static::$archiveColumnName => static::$archiveColumnNegativeValue
            ]);
        }
        return $query->limit(1)->one();
    }
}