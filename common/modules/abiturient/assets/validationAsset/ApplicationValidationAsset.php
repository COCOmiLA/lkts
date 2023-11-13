<?php

namespace common\modules\abiturient\assets\validationAsset;

use common\components\AssetManager\AssetBundleManager;

class ApplicationValidationAsset extends AssetBundleManager
{
    public $sourcePath = '@common/modules/abiturient/assets/validationAsset';

    public $js = ['js/application-validation.js'];
    public $css = ['css/validation.css'];

    public $depends = ['frontend\assets\FrontendAsset'];
}
