<?php

namespace common\modules\abiturient\views\bachelor\assets;

use yii\web\AssetBundle;

class BenefitsAsset extends AssetBundle
{
    public $sourcePath = __DIR__;

    public $css = ['css/benefits.css'];

    public $depends = [
        'frontend\assets\FrontendAsset',
    ];
}
