<?php

namespace common\modules\abiturient\assets\questionaryViewAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class QuestionaryViewAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/view.js'];

    public $depends = [FrontendAsset::class];
}
