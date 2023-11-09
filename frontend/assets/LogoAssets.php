<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class LogoAssets extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = ['css/logo.css'];

    public $depends = [
        FrontendAsset::class,
        
    ];
}
