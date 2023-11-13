<?php

namespace common\components\CommentNavigationLinkerWidget\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class CommentNavigationLinkerAssets extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['comment-navigation-linker/comment-navigation-linker.js'];

    public $depends = [FrontendAsset::class];
}
