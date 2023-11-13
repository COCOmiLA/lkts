<?php

namespace common\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class ImageInPopupViewerAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $css = ['css/image-in-popup-viewer-asset.css'];

    public $depends = [FrontendAsset::class];
}
