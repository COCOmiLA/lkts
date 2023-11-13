<?php

namespace common\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class ChangeGridViewPaginationAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/change-grid-view-pagination.js'];

    public $depends = [FrontendAsset::class];
}
