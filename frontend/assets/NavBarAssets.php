<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class NavBarAssets extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = ['css/nav_bar.css'];

    public $js = [];

    public $depends = [FrontendAsset::class];
}
