<?php

namespace common\modules\abiturient\assets\applicationAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class Select2EducationsAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/select2-educations.js'];

    public $depends = [FrontendAsset::class];
}
