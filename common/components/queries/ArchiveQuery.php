<?php

namespace common\components\queries;

use yii\db\ActiveQuery;
use yii\db\Expression;

class ArchiveQuery extends ActiveQuery
{
    public function active()
    {
        return $this->andOnCondition([
            'not', ["{$this->modelClass::tableName()}.{$this->modelClass::getArchiveColumn()}" => $this->modelClass::getArchiveValue()]
        ]);
    }

    




    public function onlyRecentlyRemovedAndActualRecords(?int $approvedAt): ActiveQuery
    {
        if (!$approvedAt) {
            return $this;
        }

        return $this->andWhere([
            'or',
            [
                'and',
                [
                    '=',
                    "{$this->modelClass::tableName()}.{$this->modelClass::getArchiveColumn()}",
                    $this->modelClass::getArchiveValue()
                ],
                ['>=', "{$this->modelClass::tableName()}.{$this->modelClass::getArchivedAtColumn()}", $approvedAt],
            ],
            [
                '!=',
                "{$this->modelClass::tableName()}.{$this->modelClass::getArchiveColumn()}",
                $this->modelClass::getArchiveValue()
            ],
        ]);
    }

    


    public function sortByArchiveFlag()
    {
        $orderByQuery = new Expression("
            {$this->modelClass::tableName()}.{$this->modelClass::getArchiveColumn()} = '{$this->modelClass::getArchiveValue()}' DESC,
            {$this->modelClass::tableName()}.{$this->modelClass::getArchiveColumn()}
        ");
        return $this->orderBy($orderByQuery);
    }
}
