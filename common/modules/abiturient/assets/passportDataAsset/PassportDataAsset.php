<?php

namespace common\modules\abiturient\assets\passportDataAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;
use common\assets\AjaxBtnManagerAsset;

class PassportDataAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/passport-data.js'];

    public $depends = [
        FrontendAsset::class,
        AjaxBtnManagerAsset::class
    ];
}
