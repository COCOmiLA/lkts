<?php

namespace common\assets;

use frontend\assets\FrontendAsset;

class ResubmissionManagementAsset extends \common\components\AssetManager\AssetBundleManager
{
    public $js = ['moderator/resubmission-management.js'];

    public $depends = [
        FrontendAsset::class,
        ChangeGridViewPaginationAsset::class,
    ];
}