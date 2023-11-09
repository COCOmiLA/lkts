<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\PollAnswer;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Thread;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;






















class PollActiveRecord extends ActiveRecord
{
    



    public $end;

    



    public $editAnswers = [];

    


    public static function tableName()
    {
        return '{{%podium_poll}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            [['question', 'votes', 'hidden', 'thread_id', 'author_id'], 'required'],
            ['question', 'string', 'max' => 255],
            ['votes', 'integer', 'min' => 1],
            ['end', 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'end_at'],
            ['end_at', 'integer'],
            ['hidden', 'boolean'],
            ['editAnswers', 'each', 'rule' => ['string', 'max' => 255]],
            ['editAnswers', 'requiredAnswers'],
        ];
    }

    


    public function requiredAnswers()
    {
        $this->editAnswers = array_unique($this->editAnswers);
        $filtered = [];
        foreach ($this->editAnswers as $answer) {
            if (!empty(trim((string)$answer))) {
                $filtered[] = trim((string)$answer);
            }
        }
        $this->editAnswers = $filtered;
        if (count($this->editAnswers) < 2) {
            $this->addError('editAnswers', Yii::t('podium/view', 'You have to add at least 2 options.'));
        }
    }

    


    public function attributeLabels()
    {
        return [
            'question' => Yii::t('podium/view', 'Question'),
            'votes' => Yii::t('podium/view', 'Number of votes'),
            'hidden' => Yii::t('podium/view', 'Hide results before voting'),
            'end' => Yii::t('podium/view', 'Poll ends at'),
        ];
    }

    



    public function getThread()
    {
        return $this->hasOne(Thread::class, ['id' => 'thread_id']);
    }

    



    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    



    public function getAnswers()
    {
        return $this->hasMany(PollAnswer::class, ['poll_id' => 'id']);
    }
}
