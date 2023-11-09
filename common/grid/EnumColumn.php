<?php
namespace common\grid;

use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;










class EnumColumn extends DataColumn
{
    


    public $enum = [];
    


    public $loadFilterDefaultValues = true;

    


    public function init()
    {
        parent::init();
        if ($this->loadFilterDefaultValues && $this->filter === null) {
            $this->filter = $this->enum;
        }
    }

    





    public function getDataCellValue($model, $key, $index)
    {
        $value = parent::getDataCellValue($model, $key, $index);
        return ArrayHelper::getValue($this->enum, $value, $value);
    }
}
