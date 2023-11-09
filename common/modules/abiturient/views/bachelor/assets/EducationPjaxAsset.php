<?php

namespace common\modules\abiturient\views\bachelor\assets;

use common\assets\AjaxBtnManagerAsset;
use common\components\AssetManager\AssetBundleManager;
use frontend\assets\FrontendAsset;
use yii\web\AssetBundle;

class EducationPjaxAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $css = ['css/education.css'];

    public $js = ['js/education.js'];

    public $depends = [
        FrontendAsset::class,
        AjaxBtnManagerAsset::class,
    ];
}
