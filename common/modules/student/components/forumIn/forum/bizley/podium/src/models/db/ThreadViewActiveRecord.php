<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;













class ThreadViewActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_thread_view}}';
    }
}
