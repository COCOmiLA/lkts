<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\models\db;

use common\modules\student\components\forumIn\forum\bizley\podium\src\db\ActiveRecord;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Category;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Post;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;



















class ForumActiveRecord extends ActiveRecord
{
    


    public static function tableName()
    {
        return '{{%podium_forum}}';
    }

    


    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
            ]
        ];
    }

    


    public function rules()
    {
        return [
            [['name', 'visible'], 'required'],
            ['visible', 'boolean'],
            [['name', 'sub'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim((string)$value));
            }],
            [['keywords', 'description'], 'string'],
        ];
    }

    



    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    



    public function getLatest()
    {
        return $this->hasOne(Post::class, ['forum_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
}
