<?php

namespace common\modules\abiturient\assets\applicationAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class ApplicationAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/application.js'];

    public $depends = [FrontendAsset::class];
}
