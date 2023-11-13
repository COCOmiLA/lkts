<?php

namespace common\components\AddressWidget\assets;

use common\components\AssetManager\AssetBundleManager;

class AddressValidatorAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['address/addressValidator.js'];

    public $depends = ['frontend\assets\FrontendAsset'];
}
