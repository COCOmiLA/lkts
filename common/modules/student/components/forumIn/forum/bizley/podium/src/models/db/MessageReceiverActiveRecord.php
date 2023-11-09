<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Message;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;














class MessageReceiverActiveRecord extends ActiveRecord
{
    


    const STATUS_NEW     = 1;
    const STATUS_READ    = 10;
    const STATUS_DELETED = 20;

    


    public $senderName;

    


    public $topic;

    


    public static function tableName()
    {
        return '{{%podium_message_receiver}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function scenarios()
    {
        return array_merge(
            parent::scenarios(),
            ['remove' => ['receiver_status']]
        );
    }

    


    public function rules()
    {
        return [
            [['receiver_id', 'message_id'], 'required'],
            [['receiver_id', 'message_id'], 'integer', 'min' => 1],
            ['receiver_status', 'in', 'range' => static::getStatuses()],
            [['senderName', 'topic'], 'string']
        ];
    }

    



    public static function getStatuses()
    {
        return [self::STATUS_NEW, self::STATUS_READ, self::STATUS_DELETED];
    }

    



    public function getMessage()
    {
        return $this->hasOne(Message::class, ['id' => 'message_id']);
    }

    



    public function getReceiver()
    {
        return $this->hasOne(User::class, ['id' => 'receiver_id']);
    }
}
