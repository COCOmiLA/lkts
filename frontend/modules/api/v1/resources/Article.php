<?php

namespace frontend\modules\api\v1\resources;

use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;




class Article extends \common\models\Article implements Linkable
{
    public function fields()
    {
        return ['id', 'slug', 'category_id', 'title', 'body', 'published_at'];
    }

    public function extraFields()
    {
        return ['category'];
    }

    




    public function getLinks()
    {
        return [
            Link::REL_SELF => Url::to(['article/view', 'id' => $this->id], true)
        ];
    }
}
