<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;


















class ActivityActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_user_activity}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    



    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
