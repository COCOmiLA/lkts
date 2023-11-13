<?php

namespace common\components\AccountingBenefits\assets;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class AccountingBenefitsComponentAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;
    
    public $js = ['accountingBenefits/form.js'];
    
    public $css = ['css/accounting-benefits.css'];
    
    public $depends = [FrontendAsset::class];
}
