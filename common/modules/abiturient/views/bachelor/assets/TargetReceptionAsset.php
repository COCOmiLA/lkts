<?php

namespace common\modules\abiturient\views\bachelor\assets;

use yii\web\AssetBundle;

class TargetReceptionAsset extends AssetBundle
{
    public $sourcePath = __DIR__;

    public $css = ['css/target_reception.css'];

    public $depends = [
        'frontend\assets\FrontendAsset',
    ];
}
