<?php

use common\components\CommentNavigationLinkerWidget\assets\CommentNavigationLinkerAssets;
use kartik\helpers\Html;
use yii\web\View;







CommentNavigationLinkerAssets::register($this);

if ($tags) {
    echo Html::tag(
        'div',
        Html::tag(
            'comment-linker-group-component',
            null,
            ['tags' => $tags]
        ),
        ['id' => 'comment-linker-group']
    );
}
