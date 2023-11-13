<?php

namespace common\widgets\ContractorField\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class ContractorFieldAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;
    
    public $js = ['contractorField/contractor-field.js'];
    
    public $css = ['css/contractor-field.css'];

    public $depends = [FrontendAsset::class];
}
