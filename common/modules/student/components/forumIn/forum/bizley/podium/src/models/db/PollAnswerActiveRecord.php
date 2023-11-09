<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;













class PollAnswerActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_poll_answer}}';
    }

    


    public function rules()
    {
        return [
            [['answer', 'poll_id'], 'required'],
            ['answer', 'string', 'max' => 255],
            ['votes', 'default', 'value' => 0],
            ['votes', 'integer', 'min' => 0],
            ['poll_id', 'integer'],
        ];
    }
}
