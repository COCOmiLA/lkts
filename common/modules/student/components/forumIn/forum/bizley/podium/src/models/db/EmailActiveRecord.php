<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

















class EmailActiveRecord extends ActiveRecord
{
    


    const STATUS_PENDING = 0;
    const STATUS_SENT    = 1;
    const STATUS_GAVEUP  = 9;

    


    public static function tableName()
    {
        return '{{%podium_email}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [['email', 'subject', 'content'], 'required'],
            ['email', 'email'],
            [['subject', 'content'], 'string'],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['attempt', 'default', 'value' => 0],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_GAVEUP]]
        ];
    }
}
