<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\widgets;

use cebe\gravatar\Gravatar;
use common\modules\student\components\forumIn\forum\bizley\podium\src\helpers\Helper;
use common\modules\student\components\forumIn\forum\bizley\podium\src\models\User;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;








class Avatar extends Widget
{
    


    public $author;

    


    public $showName = true;

    




    public function run()
    {
        $avatar = Html::img(Helper::defaultAvatar(), [
            'class' => 'podium-avatar img-circle img-responsive center-block',
            'alt' => Yii::t('podium/view', 'user deleted')
        ]);
        $name = Helper::deletedUserTag(true);
        if ($this->author instanceof User) {
            $avatar = Html::img(Helper::defaultAvatar(), [
                'class' => 'podium-avatar img-circle img-responsive center-block',
                'alt'   => Html::encode($this->author->podiumName)
            ]);
            $name = $this->author->podiumTag;
            $meta = $this->author->meta;
            if (!empty($meta)) {
                if (!empty($meta->gravatar)) {
                    $avatar = Gravatar::widget([
                        'email'        => $this->author->email,
                        'defaultImage' => 'identicon',
                        'rating'       => 'r',
                        'options'      => [
                            'alt'   => Html::encode($this->author->podiumName),
                            'class' => 'podium-avatar img-circle img-responsive center-block',
                        ]
                    ]);
                } elseif (!empty($meta->avatar)) {
                    $avatar = Html::img('@web/avatars/' . $meta->avatar, [
                        'class' => 'podium-avatar img-circle img-responsive center-block',
                        'alt'   => Html::encode($this->author->podiumName)
                    ]);
                }
            }
        }
        return $avatar . ($this->showName ? Html::tag('p', $name, ['class' => 'avatar-name']) : '');
    }
}
