<?php

namespace common\modules\student\components\forumIn\forum\bizley\podium\src\assets;

use yii\web\AssetBundle;







class PodiumAsset extends AssetBundle
{
    


    public $sourcePath = '@podium/css';

    


    public $css = ['podium.css'];

    


    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
