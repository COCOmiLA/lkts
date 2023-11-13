<?php

namespace common\models\dictionary\StoredReferenceType\RestoreHandler;


use common\models\dictionary\StoredReferenceType\RestoreHandler\interfaces\IRestoreHandler;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use common\models\ModelFrom1CByOData;
use yii\db\ActiveRecord;

class BaseRestoreHandler implements IRestoreHandler
{
    



    protected $modelClass;

    


    protected $storedReferenceType;

    



    protected $modelRefColumn;
    



    protected $modelRefRelation;

    



    protected $modelRefTypeComparisonColumn = 'reference_uid';
    



    protected $archiveQuery = ['archive' => false];


    public function __construct($storedReferenceType, $modelClass, $modelRefColumn, $archiveQuery = null)
    {
        $this->modelClass = $modelClass;
        $this->modelRefColumn = $modelRefColumn;
        $this->storedReferenceType = $storedReferenceType;

        if ($archiveQuery !== null) {
            $this->archiveQuery = $archiveQuery;
        }
    }

    public function setModelRefTypeComparisonColumn(string $col_name)
    {
        $this->modelRefTypeComparisonColumn = $col_name;
        return $this;
    }

    public function getModelRefTypeComparisonColumn()
    {
        return $this->modelRefTypeComparisonColumn;
    }

    


    public function getModelClass(): string
    {
        return $this->modelClass;
    }

    



    public function setModelClass(string $modelClass): BaseRestoreHandler
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    public function getArchiveQuery(): ?array
    {
        return $this->archiveQuery;
    }

    



    public function setArchiveQuery(?array $archiveQuery): BaseRestoreHandler
    {
        $this->archiveQuery = $archiveQuery;
        return $this;
    }

    


    public function getModelRefColumn(): string
    {
        return $this->modelRefColumn;
    }

    



    public function setModelRefColumn(string $modelRefColumn): BaseRestoreHandler
    {
        $this->modelRefColumn = $modelRefColumn;
        return $this;
    }

    


    public function getStoredReferenceType(): ActiveRecord
    {
        return $this->storedReferenceType;
    }

    public function restore()
    {
        $arr = self::getStoredReferenceType()::find()
            ->andWhere([
                self::getModelRefTypeComparisonColumn() => self::getStoredReferenceType()->{self::getModelRefTypeComparisonColumn()},
            ])
            ->andWhere(['not', [self::getStoredReferenceType()::tableName() . '.id' => self::getStoredReferenceType()->id]])
            ->select('id');

        if ($arr->exists()) {
            $select_query = [
                self::getModelRefColumn() => $arr
            ];
            if (self::getArchiveQuery()) {
                $select_query = [
                    'and',
                    $select_query,
                    self::getArchiveQuery()
                ];
            }
            return self::getModelClass()::updateAll(
                [self::getModelRefColumn() => self::getStoredReferenceType()->id],
                $select_query
            );
        }

        return null;
    }
}
