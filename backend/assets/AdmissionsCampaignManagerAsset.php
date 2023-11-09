<?php

namespace backend\assets;

use common\components\AssetManager\AssetBundleManager;

class AdmissionsCampaignManagerAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['admin_assets/admissions-campaign-manager.js'];

    public $depends = [BackendAsset::class];
}
