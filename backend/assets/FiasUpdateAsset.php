<?php

namespace backend\assets;

use common\components\AssetManager\AssetBundleManager;

class FiasUpdateAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['admin_assets/fias_update.js'];

    public $depends = [BackendAsset::class];
}