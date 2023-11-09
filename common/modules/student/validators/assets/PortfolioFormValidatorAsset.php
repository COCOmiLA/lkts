<?php

namespace common\modules\student\validators\assets;

use common\components\AssetManager\AssetBundleManager;

class PortfolioFormValidatorAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['portfolio_form_validator/portfolio_form_validator_asset.js'];
}
