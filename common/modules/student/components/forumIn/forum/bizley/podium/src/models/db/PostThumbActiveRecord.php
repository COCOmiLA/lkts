<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;













class PostThumbActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_post_thumb}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }
}
