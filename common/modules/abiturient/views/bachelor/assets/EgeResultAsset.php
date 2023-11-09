<?php

namespace common\modules\abiturient\views\bachelor\assets;

use common\components\AssetManager\AssetBundleManager;

class EgeResultAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['egeResult/egeResult.js'];
    public $css = ['css/egeResult.css'];

    public $depends = [
        'frontend\assets\FrontendAsset',
    ];
}
