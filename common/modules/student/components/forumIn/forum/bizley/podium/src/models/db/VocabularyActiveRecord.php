<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Post;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Thread;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;







class VocabularyActiveRecord extends ActiveRecord
{
    


    public $query;

    


    public $thread_id;

    


    public $post_id;

    


    public static function tableName()
    {
        return '{{%podium_vocabulary}}';
    }

    


    public function rules()
    {
        return [
            ['query', 'string'],
            ['query', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim((string)$value));
            }],
        ];
    }

    



    public function getThread()
    {
        return $this->hasOne(Thread::class, ['id' => 'thread_id']);
    }

    



    public function getPostData()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    



    public function getPosts()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])->viaTable('{{%podium_vocabulary_junction}}', ['word_id' => 'id']);
    }
}
