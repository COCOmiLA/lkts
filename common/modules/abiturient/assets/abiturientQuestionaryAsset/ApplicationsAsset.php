<?php

namespace common\modules\abiturient\assets\abiturientQuestionaryAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class ApplicationsAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $css = ['css/applications.css'];

    public $depends = [FrontendAsset::class];
}
