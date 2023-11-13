<?php

namespace common\components\queries;

class DictionaryQuery extends ArchiveQuery
{
    public function notMarkedToDelete()
    {
        return $this
            ->andWhere(['not', ["{$this->modelClass::tableName()}.{$this->modelClass::getDeletionMarkColumnName()}" => true]]);
    }
}