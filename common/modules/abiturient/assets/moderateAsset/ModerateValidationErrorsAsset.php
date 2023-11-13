<?php

namespace common\modules\abiturient\assets\moderateAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class ModerateValidationErrorsAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/validation-errors.js'];

    public $depends = [FrontendAsset::class];
}
