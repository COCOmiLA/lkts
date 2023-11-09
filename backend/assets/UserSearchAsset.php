<?php

namespace backend\assets;

use common\assets\ChangeGridViewPaginationAsset;
use common\components\AssetManager\AssetBundleManager;

class UserSearchAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['admin_assets/user-search.js'];

    public $css = ['css/user-search.css'];

    public $depends = [
        BackendAsset::class,
        ChangeGridViewPaginationAsset::class
    ];
}
