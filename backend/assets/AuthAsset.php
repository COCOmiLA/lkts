<?php

namespace backend\assets;

use common\components\AssetManager\AssetBundleManager;

class AuthAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['admin_assets/auth.js'];

    public $depends = [BackendAsset::class];
}
