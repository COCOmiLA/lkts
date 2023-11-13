<?php

namespace common\modules\abiturient\assets\abiturientQuestionaryAsset;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class AbiturientQuestionaryAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $css = ['css/abiturient-questionary.css'];
    
    public $js = ['js/abiturient-questionary.js'];

    public $depends = [FrontendAsset::class];
}
