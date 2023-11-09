<?php

namespace common\modules\abiturient\views\bachelor\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\DepdropFixedInheritanceAsset;
use frontend\assets\FrontendAsset;

class ApplicationChangeHistoryAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $css = ['css/applicationChangeHistory.css'];

    public $depends = [
        FrontendAsset::class,
        DepdropFixedInheritanceAsset::class,
    ];
}
