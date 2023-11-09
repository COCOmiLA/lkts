<?php

namespace common\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class TooltipAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;
    
    public $js = ['js/tooltipInit.js'];
    
    public $depends = [FrontendAsset::class];
}
