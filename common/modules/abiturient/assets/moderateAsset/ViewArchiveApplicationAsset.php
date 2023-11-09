<?php

namespace common\modules\abiturient\assets\moderateAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class ViewArchiveApplicationAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/view-archive-application.js'];
    public $css = ['css/view-archive-application.css'];

    public $depends = [FrontendAsset::class];
}
