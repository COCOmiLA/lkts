<?php

namespace frontend\assets;

class IndividualAchievementsFillAsset extends \common\components\AssetManager\AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['individual_achievement/ia_autofill.js'];

    public $depends = [
        'frontend\assets\FrontendAsset',
    ];
}