<?php

namespace common\models\query;

use yii\base\InvalidConfigException;
use yii\db\QueryInterface;





class ActiveRecordDataProvider extends \yii\data\ActiveDataProvider
{
    



    public string $primary_column;

    


    protected function prepareModels()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        $orders = null;
        
        if (($sort = $this->getSort()) !== false) {
            $orders = $sort->getOrders();
            $query->addOrderBy($orders);
        }

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->totalCount === 0) {
                return [];
            }
            $primary_columns = $query->select($this->primary_column)->limit(-1)->offset(-1)->column($this->db);
            $query = (clone $this->query)
                ->andWhere([$this->primary_column => $this->processPrimaryColumns($primary_columns, $pagination->getLimit(), $pagination->getOffset())])
                ->limit(-1)
                ->offset(-1);
            
            if ($orders) {
                $query->addOrderBy($sort->getOrders());
            }
        }

        return $query->all($this->db);
    }

    private function processPrimaryColumns(array $primary_values, int $limit, int $offset): array
    {
        $primary_values = array_values(array_unique($primary_values));
        if ($limit <= 0) {
            $limit = null;
        }
        if ($offset < 0) {
            $offset = 0;
        }
        return array_slice($primary_values, $offset, $limit);
    }

    


    protected function prepareTotalCount()
    {
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;
        return (int)$query->select($this->primary_column)->distinct()->limit(-1)->offset(-1)->orderBy([])->count('*', $this->db);
    }
}