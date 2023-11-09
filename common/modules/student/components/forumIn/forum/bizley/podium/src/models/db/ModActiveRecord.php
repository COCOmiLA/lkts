<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use yii\db\ActiveQuery;











class ModActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_moderator}}';
    }

    



    public function getForum()
    {
        return $this->hasOne(static::class, ['id' => 'forum_id']);
    }
}
