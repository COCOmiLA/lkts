<?php

namespace common\modules\abiturient\assets\chosenApplicationAsset;

use common\components\AssetManager\AssetBundleManager;

class ChosenApplicationAsset extends AssetBundleManager
{
    public $sourcePath = '@common/modules/abiturient/assets/chosenApplicationAsset';
    
    public $css = ['css/chosen_application.css'];
}
