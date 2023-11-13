<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\db\ActiveRecord as YiiActiveRecord;
use yii\db\Connection;







class ActiveRecord extends YiiActiveRecord
{
    





    public static function getDb()
    {
        return Podium::getInstance()->db;
    }
}