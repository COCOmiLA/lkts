<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets\gridview;

use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use yii\grid\DataColumn as YiiDataColumn;







class DataColumn extends YiiDataColumn
{
    


    public $encodeLabel = false;

    


    protected function getHeaderCellLabel()
    {
        if (!empty($this->attribute)) {
            return parent::getHeaderCellLabel() . Helper::sortOrder($this->attribute);
        }
        return parent::getHeaderCellLabel();
    }
}
