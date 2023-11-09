<?php

namespace common\assets;

use yii\web\AssetBundle;

class MomentJs extends AssetBundle
{
    public $sourcePath = '@bower/moment';
    public $js = [
        'min/moment-with-locales.min.js'
    ];
    
    public $depends = [
        \yii\web\YiiAsset::class,
        \yii\bootstrap4\BootstrapAsset::class
    ];
}

