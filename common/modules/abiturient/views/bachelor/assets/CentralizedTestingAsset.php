<?php

namespace common\modules\abiturient\views\bachelor\assets;

use common\components\AssetManager\AssetBundleManager;

class CentralizedTestingAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/centralizedTesting.js'];
    public $css = ['css/centralized_testing.css'];

    public $depends = ['frontend\assets\FrontendAsset'];
}
