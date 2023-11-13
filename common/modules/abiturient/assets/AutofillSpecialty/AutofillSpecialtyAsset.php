<?php

namespace common\modules\abiturient\assets\AutofillSpecialty;

use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;

class AutofillSpecialtyAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $css = ['css/autofill-specialty.css'];

    public $depends = [FrontendAsset::class];
}
