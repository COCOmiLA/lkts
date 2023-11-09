<?php

namespace common\assets;

use common\components\AssetManager\AssetBundleManager;

class MobileFriendlyTablesAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $css = ['css/mobile_friendly_tables.css'];
    public $js = ['js/mobile_friendly_tables.js'];
}