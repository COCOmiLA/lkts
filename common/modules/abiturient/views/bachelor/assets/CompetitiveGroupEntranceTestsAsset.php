<?php

namespace common\modules\abiturient\views\bachelor\assets;

use common\components\AssetManager\AssetBundleManager;

class CompetitiveGroupEntranceTestsAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['competitiveGroup/competitiveGroupEntranceTests.js'];
    public $css = ['css/competitiveGroupEntranceTests.css'];

    public $depends = [
        'frontend\assets\FrontendAsset',
    ];
}
