<?php

namespace common\components\AddressWidget\assets;

use common\components\AssetManager\AssetBundleManager;

class AddressWidgetAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['address/addressWidget.js'];

    public $depends = ['frontend\assets\FrontendAsset'];
}
