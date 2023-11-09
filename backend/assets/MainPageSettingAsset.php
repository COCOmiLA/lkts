<?php

namespace backend\assets;

use common\assets\ChangeGridViewPaginationAsset;
use common\components\AssetManager\AssetBundleManager;

class MainPageSettingAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['admin_assets/main-page-setting.js'];

    public $depends = [
        BackendAsset::class,
        ChangeGridViewPaginationAsset::class
    ];
}
