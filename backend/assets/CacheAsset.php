<?php

namespace backend\assets;

use yii\web\AssetBundle;

class CacheAsset extends AssetBundle
{
    public $sourcePath = __DIR__;

    public $css = ['css/cache.css'];

    public $depends = [BackendAsset::class];
}
