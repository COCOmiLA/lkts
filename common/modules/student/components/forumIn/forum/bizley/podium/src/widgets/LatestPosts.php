<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets;

use common\modules\student\components\forumIn\forum\bizley\podium\src\models\Post;
use common\modules\student\components\forumIn\forum\bizley\podium\src\Podium;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;








class LatestPosts extends Widget
{
    


    public $posts = 5;

    



    public function run()
    {
        $out = Html::beginTag('div', ['class' => 'panel panel-default']) . "\n";
        $out .= Html::tag('div', Yii::t('podium/view', 'Latest posts'), ['class' => 'panel-heading']) . "\n";

        $latest = Post::getLatest(is_numeric($this->posts) && $this->posts > 0 ? $this->posts : 5);

        if ($latest) {
            $out .= Html::beginTag('table', ['class' => 'table table-hover']) . "\n";
            foreach ($latest as $post) {
                $out .= Html::beginTag('tr');
                $out .= Html::beginTag('td');
                $out .= Html::a($post['title'], ['forum/show', 'id' => $post['id']], ['class' => 'center-block']) . "\n";
                $out .= Html::tag('small', Podium::getInstance()->formatter->asRelativeTime($post['created']) . "\n" . $post['author']) . "\n";
                $out .= Html::endTag('td');
                $out .= Html::endTag('tr');
            }
            $out .= Html::endTag('table') . "\n";
        } else {
            $out .= Html::beginTag('div', ['class' => 'panel-body']) . "\n";
            $out .= Html::tag('small', Yii::t('podium/view', 'No posts have been added yet.')) . "\n";
            $out .= Html::endTag('div') . "\n";
        }

        $out .= Html::endTag('div') . "\n";

        return $out;
    }
}
