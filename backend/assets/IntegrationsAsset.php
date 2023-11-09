<?php

namespace backend\assets;

use common\components\AssetManager\AssetBundleManager;

class IntegrationsAsset extends AssetBundleManager
{
    public $js = ['admin_assets/integrations.js'];

    public $depends = [
        BackendAsset::class,
    ];
}