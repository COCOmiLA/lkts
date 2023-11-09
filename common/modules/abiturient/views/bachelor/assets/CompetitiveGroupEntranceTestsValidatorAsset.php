<?php

namespace common\modules\abiturient\views\bachelor\assets;

use common\components\AssetManager\AssetBundleManager;

class CompetitiveGroupEntranceTestsValidatorAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/competitiveGroupEntranceTestsValidatorAsset.js'];

    public $depends = [
        'frontend\assets\FrontendAsset',
    ];
}
