<?php

namespace common\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class PortalTabularFormAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/tabular_script.js'];

    public $depends = [
        FrontendAsset::class,
        AjaxBtnManagerAsset::class,
    ];
}