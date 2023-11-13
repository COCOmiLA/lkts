<?php

namespace common\components\behaviors\timestampBehavior;

use common\components\DateTimeHelper;
use yii\behaviors\TimestampBehavior;




class TimestampBehaviorMilliseconds extends TimestampBehavior
{
    


    protected function getValue($event)
    {
        if ($this->value === null) {
            return DateTimeHelper::mstime();
        }
        
        parent::getValue($event);
    }
}
