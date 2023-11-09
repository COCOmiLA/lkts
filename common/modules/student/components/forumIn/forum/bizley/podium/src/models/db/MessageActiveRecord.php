<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\MessageReceiver;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;
















class MessageActiveRecord extends ActiveRecord
{
    


    const STATUS_NEW     = 1;
    const STATUS_READ    = 10;
    const STATUS_DELETED = 20;

    


    const MAX_RECEIVERS = 10;
    const SPAM_MESSAGES = 10;
    const SPAM_WAIT     = 1;

    


    public $receiversId;

    



    public $friendsId;

    


    public static function tableName()
    {
        return '{{%podium_message}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function scenarios()
    {
        return array_merge(
            parent::scenarios(),
            [
                'report' => ['content'],
                'remove' => ['sender_status'],
            ]
        );
    }

    


    public function rules()
    {
        return [
            [['topic', 'content'], 'required'],
            [['receiversId', 'friendsId'], 'each', 'rule' => ['integer', 'min' => 1]],
            ['sender_status', 'in', 'range' => self::getStatuses()],
            ['topic', 'string', 'max' => 255],
            ['topic', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process(trim((string)$value));
            }],
            ['content', 'filter', 'filter' => function($value) {
                if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
                    return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig('markdown'));
                }
                return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig());
            }],
        ];
    }

    



    public static function getStatuses()
    {
        return [self::STATUS_NEW, self::STATUS_READ, self::STATUS_DELETED];
    }

    



    public static function getInboxStatuses()
    {
        return [self::STATUS_NEW, self::STATUS_READ];
    }

    



    public static function getSentStatuses()
    {
        return [self::STATUS_READ];
    }

    



    public static function getDeletedStatuses()
    {
        return [self::STATUS_DELETED];
    }

    



    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id']);
    }

    



    public function getMessageReceivers()
    {
        return $this->hasMany(MessageReceiver::class, ['message_id' => 'id']);
    }

    



    public function getReply()
    {
        return $this->hasOne(static::class, ['id' => 'replyto']);
    }
}
