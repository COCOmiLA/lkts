<?php

namespace common\components\tree\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;
use sguinfocom\widget\TreeViewAsset;

class TreeParserAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['tree-parser/tree-parser.js'];
    public $css = ['css/tree-parser.css'];

    public $depends = [
        FrontendAsset::class,
        TreeViewAsset::class,
    ];
}
