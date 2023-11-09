<?php

namespace common\modules\abiturient\assets\abiturientHeaderAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class AbiturientHeaderAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/abiturientHeader.js'];

    public $depends = [FrontendAsset::class];
}
