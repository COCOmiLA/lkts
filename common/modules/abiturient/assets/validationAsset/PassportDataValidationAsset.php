<?php

namespace common\modules\abiturient\assets\validationAsset;

use common\components\AssetManager\AssetBundleManager;

class PassportDataValidationAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;
    public $js = ['js/passport-data-validation.js'];

    public $depends = ['frontend\assets\FrontendAsset'];
}
