<?php

namespace common\modules\abiturient\assets\changeHistoryAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class ChangeHistoryAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/changeHistoryModalButton.js'];

    public $depends = [FrontendAsset::class];
}
