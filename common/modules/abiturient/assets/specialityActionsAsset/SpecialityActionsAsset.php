<?php

namespace common\modules\abiturient\assets\specialityActionsAsset;

use common\components\AssetManager\AssetBundleManager;

class SpecialityActionsAsset extends AssetBundleManager
{
    public $sourcePath = '@common/modules/abiturient/assets/specialityActionsAsset';
    
    public $js = ['js/specialities.js'];

    public $depends = ['frontend\assets\FrontendAsset'];
}
