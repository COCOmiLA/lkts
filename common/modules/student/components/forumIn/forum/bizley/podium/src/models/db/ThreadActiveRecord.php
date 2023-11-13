<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Forum;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Poll;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Post;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Subscription;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\ThreadView;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\BaseInflector;
use yii\helpers\HtmlPurifier;






















class ThreadActiveRecord extends ActiveRecord
{
    


    public $post;

    


    public $subscribe;

    



    public $pollAdded = 0;

    



    public $pollQuestion;

    



    public $pollVotes = 1;

    



    public $pollAnswers = [];

    



    public $pollEnd;

    



    public $pollHidden = 0;

    


    public static function tableName()
    {
        return '{{%podium_thread}}';
    }

    


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
            ],
        ];
    }

    


    public function rules()
    {
        return [
            [
                'name',
                'required',
                'message' => Yii::t('podium/view', 'Topic can not be blank.'),
            ],
            [
                'post',
                'required',
                'on' => ['new']
            ],
            [
                'post',
                'string',
                'min' => 10,
                'on' => ['new']
            ],
            [
                'post',
                'filter',
                'filter' => function ($value) {
                    if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
                        return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig('markdown'));
                    }
                    return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig('full'));
                },
                'on' => ['new']
            ],
            [
                'pinned',
                'boolean'
            ],
            [
                'subscribe',
                'boolean'
            ],
            [
                'name',
                'filter',
                'filter' => function ($value) {
                    $value = HtmlPurifier::process(trim((string)$value));
                    $slug = BaseInflector::slug($value);
                    if (empty($slug)) {
                        $this->addError(
                            'name',
                            Yii::t('podium/view', 'Invalid topic name.')
                        );
                        return null;
                    }
                    return $value;
                }
            ],
            [
                'pollQuestion',
                'string',
                'max' => 255
            ],
            [
                'pollVotes',
                'integer',
                'min' => 1,
                'max' => 10
            ],
            [
                'pollAnswers',
                'each',
                'rule' => [
                    'string',
                    'max' => 255
                ]
            ],
            [
                'pollEnd',
                'date',
                'format' => 'yyyy-MM-dd'
            ],
            [
                [
                    'pollHidden',
                    'pollAdded'
                ],
                'boolean'
            ],
            [
                'pollAnswers',
                'requiredPollAnswers'
            ],
            [
                [
                    'pollQuestion',
                    'pollVotes'
                ],
                'required',
                'when' => function ($model) {
                    return $model->pollAdded;
                }, 'whenClient' => 'function (attribute, value) { return $("#poll_added").val() == 1; }'
            ],
        ];
    }

    



    public function requiredPollAnswers()
    {
        if ($this->pollAdded) {
            $this->pollAnswers = array_unique($this->pollAnswers);
            $filtered = [];
            foreach ($this->pollAnswers as $answer) {
                if (!empty(trim((string)$answer))) {
                    $filtered[] = trim((string)$answer);
                }
            }
            $this->pollAnswers = $filtered;
            if (count($this->pollAnswers) < 2) {
                $this->addError('pollAnswers', Yii::t('podium/view', 'You have to add at least 2 options.'));
            }
        }
    }

    




    public function attributeLabels()
    {
        return [
            'pollEnd' => Yii::t('podium/view', 'Poll ends at'),
            'pollAuestion' => Yii::t('podium/view', 'Question'),
            'pollVotes' => Yii::t('podium/view', 'Number of votes'),
            'pollHidden' => Yii::t('podium/view', 'Hide results before voting'),
        ];
    }

    



    public function getForum()
    {
        return $this->hasOne(Forum::class, ['id' => 'forum_id']);
    }

    




    public function getPoll()
    {
        return $this->hasOne(Poll::class, ['thread_id' => 'id']);
    }

    



    public function getUserView()
    {
        return $this->hasOne(ThreadView::class, ['thread_id' => 'id'])->where(['user_id' => User::loggedId()]);
    }

    



    public function getThreadView()
    {
        return $this->hasMany(ThreadView::class, ['thread_id' => 'id']);
    }

    



    public function getSubscription()
    {
        return $this->hasOne(Subscription::class, ['thread_id' => 'id'])->where(['user_id' => User::loggedId()]);
    }

    



    public function getLatest()
    {
        return $this->hasOne(Post::class, ['thread_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }

    




    public function getPostsCount()
    {
        return Post::find()->where(['thread_id' => $this->id])->count('id');
    }

    



    public function getPostData()
    {
        return $this->hasOne(Post::class, ['thread_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }

    



    public function getFirstNewNotSeen()
    {
        return $this
            ->hasOne(Post::class, ['thread_id' => 'id'])
            ->where(['>', 'created_at', $this->userView ? $this->userView->new_last_seen : 0])
            ->orderBy(['id' => SORT_ASC]);
    }

    



    public function getFirstEditedNotSeen()
    {
        return $this
            ->hasOne(Post::class, ['thread_id' => 'id'])
            ->where(['>', 'edited_at', $this->userView ? $this->userView->edited_last_seen : 0])
            ->orderBy(['id' => SORT_ASC]);
    }

    



    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }
}
