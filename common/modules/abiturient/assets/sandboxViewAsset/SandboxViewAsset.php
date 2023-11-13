<?php

namespace common\modules\abiturient\assets\sandboxViewAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class SandboxViewAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/view.js'];

    public $depends = [FrontendAsset::class];
}
