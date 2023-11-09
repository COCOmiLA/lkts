<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Forum;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\PostThumb;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Thread;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;

















class PostActiveRecord extends ActiveRecord
{
    


    public $subscribe;

    


    public $topic;

    


    public static function tableName()
    {
        return '{{%podium_post}}';
    }

    


    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    


    public function rules()
    {
        return [
            ['topic', 'required', 'message' => Yii::t('podium/view', 'Topic can not be blank.'), 'on' => ['firstPost']],
            ['topic', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim((string)$value));
            }, 'on' => ['firstPost']],
            ['subscribe', 'boolean'],
            ['content', 'required'],
            ['content', 'filter', 'filter' => function ($value) {
                if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
                    return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig('markdown'));
                }
                return HtmlPurifier::process(trim((string)$value), Helper::podiumPurifierConfig('full'));
            }],
            ['content', 'string', 'min' => 10],
        ];
    }

    



    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    



    public function getThread()
    {
        return $this->hasOne(Thread::class, ['id' => 'thread_id']);
    }

    



    public function getForum()
    {
        return $this->hasOne(Forum::class, ['id' => 'forum_id']);
    }

    



    public function getThumb()
    {
        return $this->hasOne(PostThumb::class, ['post_id' => 'id'])->andWhere(['user_id' => User::loggedId()]);
    }
}
