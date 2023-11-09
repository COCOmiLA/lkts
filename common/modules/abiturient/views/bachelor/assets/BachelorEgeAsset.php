<?php

namespace common\modules\abiturient\views\bachelor\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\DepdropFixedInheritanceAsset;

class BachelorEgeAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['bachelor_ege/bachelorEge.js'];
    public $css = ['css/bachelorEge.css'];

    public $depends = [
        'frontend\assets\FrontendAsset',
        DepdropFixedInheritanceAsset::class
    ];
}
