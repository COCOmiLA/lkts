<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Thread;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use yii\db\ActiveQuery;















class SubscriptionActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_subscription}}';
    }

    



    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    



    public function getThread()
    {
        return $this->hasOne(Thread::class, ['id' => 'thread_id']);
    }
}
