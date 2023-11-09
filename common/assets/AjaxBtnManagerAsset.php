<?php

namespace common\assets;

use common\components\AssetManager\AssetBundleManager;

class AjaxBtnManagerAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/ajaxBtnManager.js'];
    public $css = ['css/ajaxBtnManager.css'];

    public $depends = [
        \yii\web\YiiAsset::class,
    ];
}
