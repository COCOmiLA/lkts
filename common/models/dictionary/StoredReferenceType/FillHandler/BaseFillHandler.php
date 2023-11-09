<?php

namespace common\models\dictionary\StoredReferenceType\FillHandler;


use common\models\dictionary\StoredReferenceType\FillHandler\interfaces\IBaseFillHandler;
use common\models\dictionary\StoredReferenceType\StoredReferenceType;
use yii\helpers\ArrayHelper;

class BaseFillHandler implements IBaseFillHandler
{
    



    protected $modelClass;

    


    protected $storedReferenceType;

    



    protected $modelRefColumn;

    



    protected $modelCodeColumn = 'code';
    



    protected $modelRefTypeComparisonColumn = 'reference_id';
    



    protected $archiveQuery = ['archive' => false];


    public function __construct($storedReferenceType, $modelClass, $modelRefColumn, $modelCodeColumn, $archiveQuery = null)
    {
        $this->modelClass = $modelClass;
        $this->modelRefColumn = $modelRefColumn;
        $this->storedReferenceType = $storedReferenceType;

        if ($archiveQuery !== null) {
            $this->archiveQuery = $archiveQuery;
        }

        if ($modelCodeColumn !== null) {
            $this->modelCodeColumn = $modelCodeColumn;
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

    



    public function setModelClass(string $modelClass): BaseFillHandler
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    public function getArchiveQuery(): ?array
    {
        return $this->archiveQuery;
    }

    



    public function setArchiveQuery(array $archiveQuery): BaseFillHandler
    {
        $this->archiveQuery = $archiveQuery;
        return $this;
    }

    


    public function getModelRefColumn(): string
    {
        return $this->modelRefColumn;
    }

    



    public function setModelRefColumn(string $modelRefColumn): BaseFillHandler
    {
        $this->modelRefColumn = $modelRefColumn;
        return $this;
    }

    


    public function getStoredReferenceType(): StoredReferenceType
    {
        return $this->storedReferenceType;
    }

    


    public function getModelCodeColumn(): string
    {
        return $this->modelCodeColumn;
    }

    


    public function fill()
    {
        return self::getModelClass()::updateAll([
            self::getModelRefColumn() => self::getStoredReferenceType()->id
        ], ArrayHelper::merge([
            self::getModelCodeColumn() => self::getStoredReferenceType()->{self::getModelRefTypeComparisonColumn()}
        ], self::getArchiveQuery()));
    }
}