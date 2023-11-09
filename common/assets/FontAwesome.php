<?php

namespace common\assets;

use yii\web\AssetBundle;

class FontAwesome extends AssetBundle
{
    public $sourcePath = '@bower/font-awesome';

    
    public $css = [
        'https://use.fontawesome.com/releases/v5.6.1/css/all.css',
        'css/font-awesome.min.css',
    ];
}
