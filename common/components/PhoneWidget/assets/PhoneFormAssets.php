<?php

namespace common\components\PhoneWidget\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class PhoneFormAssets extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['PhoneWidget/phone-form.js'];
    public $css = [
        'css/intlTelInput.css',
        'css/phone-form.css',
    ];

    public $depends = [FrontendAsset::class];
}
