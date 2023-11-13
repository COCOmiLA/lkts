<?php

namespace common\models\traits;

trait ColumnExistsTrait
{
    private function checkIfColumnExists($columnName)
    {
        $class = get_called_class();
        $table = \Yii::$app->db->getTableSchema($class::tableName());
        return isset($table->columns[$columnName]);
    }
}