<?php

namespace common\modules\abiturient\assets\moderateAsset;

use common\components\AssetManager\AssetBundleManager;

class ModerateAsset extends AssetBundleManager
{
    public $sourcePath = '@common/modules/abiturient/assets/moderateAsset';

    public $js = ['js/moderate.js'];
    public $css = ['css/moderate.css'];

    public $depends = ['frontend\assets\FrontendAsset'];
}
