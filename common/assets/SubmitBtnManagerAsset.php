<?php

namespace common\assets;

use common\components\AssetManager\AssetBundleManager;

class SubmitBtnManagerAsset extends AssetBundleManager
{
    public $sourcePath = __DIR__;

    public $js = ['js/submitBtnManager.js'];
    public $css = ['css/submitBtnManager.css'];

    public $depends = [
        \yii\web\YiiAsset::class,
    ];
}
